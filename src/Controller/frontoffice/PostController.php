<?php

namespace App\Controller\frontoffice;

use App\Entity\Commentaires;
use App\Entity\Group;
use App\Entity\Posts;
use App\Entity\Reactions;
use App\Entity\User;
use App\Form\PostType;
use App\Service\ModerationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class PostController extends AbstractController
{
    public function __construct(private readonly ModerationService $moderation) {}

    #[Route('/groups/{id}/post/new', name: 'group_post_new')]
    public function new(Request $request, EntityManagerInterface $em, ?Group $group = null): Response
    {
        $user = $this->resolveCurrentUser($request, $em);
        if (!$user) {
            $this->addFlash('error', 'You must be logged in to create a post.');
            if ($group) {
                return $this->redirectToRoute('group_show', ['id' => $group->getId()]);
            }
            return $this->redirectToRoute('groups_index');
        }

        $post = new Posts();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setAuthorId($user);
            $post->setCreatedAt(new \DateTimeImmutable());
            $post->setLikesCounter(0);
            $post->setStatus('published');

            if ($group) {
                $post->setGroupId($group);
            }

            [$absoluteFilePath, $relativeFilePath] = $this->uploadAttachedFile($form->get('attached_file')->getData());

            $textToCheck = trim($post->getTitre() . ' ' . $post->getDescription());
            $moderationResult = $this->moderation->moderatePost($textToCheck, $absoluteFilePath);

            if (!$moderationResult['safe']) {
                if ($absoluteFilePath && is_file($absoluteFilePath)) {
                    @unlink($absoluteFilePath);
                }

                $this->addFlash('moderation_error', $this->moderationErrorMessage((string) ($moderationResult['reason'] ?? '')));

                return $this->render('frontoffice/posts/new.html.twig', [
                    'group' => $group,
                    'form' => $form->createView(),
                ]);
            }

            if (!empty($moderationResult['warning'])) {
                $this->addFlash('warning', (string) $moderationResult['warning']);
            }

            if ($relativeFilePath !== null) {
                $post->setAttachedFile($relativeFilePath);
            }

            $em->persist($post);
            $em->flush();

            $this->addFlash('success', 'Post created successfully!');
            if ($group) {
                return $this->redirectToRoute('group_show', ['id' => $group->getId()]);
            }
            if ($post->getGroupId()) {
                return $this->redirectToRoute('group_show', ['id' => $post->getGroupId()->getId()]);
            }
            return $this->redirectToRoute('groups_index');
        }

        return $this->render('frontoffice/posts/new.html.twig', [
            'group' => $group,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/posts/{id}/like', name: 'post_like')]
    public function like(Request $request, EntityManagerInterface $em, Posts $post): Response
    {
        $user = $this->resolveCurrentUser($request, $em);
        if (!$user) {
            $this->addFlash('error', 'You must be logged in to like a post.');
            return $this->redirectToRoute('groups_index');
        }

        $reactionRepo = $em->getRepository(Reactions::class);
        $existing = $reactionRepo->findOneBy([
            'user_id' => $user,
            'post_id' => $post,
            'type' => 'like',
        ]);

        if ($existing) {
            $em->remove($existing);
            $post->setLikesCounter(max(0, ($post->getLikesCounter() ?? 0) - 1));
        } else {
            $reaction = new Reactions();
            $reaction->setUserId($user);
            $reaction->setPostId($post);
            $reaction->setType('like');
            $reaction->setPostedAt(new \DateTimeImmutable());
            $reaction->setUrl($request->headers->get('referer') ?? '');
            $em->persist($reaction);
            $post->setLikesCounter(($post->getLikesCounter() ?? 0) + 1);
        }

        $em->flush();
        return $this->redirectAfterPostContext($request, $post);
    }

    #[Route('/posts/{id}/comment', name: 'post_comment', methods: ['POST'])]
    public function comment(Request $request, EntityManagerInterface $em, Posts $post): Response
    {
        $user = $this->resolveCurrentUser($request, $em);
        if (!$user) {
            $this->addFlash('error', 'You must be logged in to comment.');
            return $this->redirectToRoute('groups_index');
        }

        $content = trim((string) $request->request->get('content'));
        if ($content !== '') {
            $comment = new Commentaires();
            $comment->setContent($content);
            $comment->setCreatedAt(new \DateTimeImmutable());
            $comment->setLikes(0);
            $comment->setAuthorId($user);
            $comment->setPost($post);
            $em->persist($comment);
            $em->flush();
        }

        return $this->redirectAfterPostContext($request, $post);
    }

    #[Route('/comment/{id}/report', name: 'comment_report', methods: ['POST'])]
    public function reportComment(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        MailerInterface $mailer,
        Environment $twig
    ): Response {
        // Get current user
        $user = $this->getUser();
        if (!$user) {
            $sessionUserId = $request->getSession()->get('user_id');
            if ($sessionUserId) {
                $user = $em->getRepository(User::class)->find($sessionUserId);
            }
        }
        if (!$user) {
            $this->addFlash('error', 'You must be logged in to report a comment.');
            return $this->redirectToRoute('groups_index');
        }

        // Find the comment
        $comment = $em->getRepository(Commentaires::class)->find($id);
        if (!$comment) {
            $this->addFlash('error', 'Comment not found.');
            return $this->redirectToRoute('groups_index');
        }

        // Get comment author
        $commentAuthor = $comment->getAuthorId();

        // Prevent self-reporting
        if ($commentAuthor->getId() === $user->getId()) {
            $this->addFlash('error', 'You cannot report your own comment.');
            $referer = $request->headers->get('referer');
            return $referer ? $this->redirect($referer) : $this->redirectToRoute('groups_index');
        }

        // Increment report count
        $newReportCount = $commentAuthor->getReportNbr() + 1;
        $commentAuthor->setReportNbr($newReportCount);

        // At 4 reports → send warning email
        if ($newReportCount == 4) {
            try {
                $htmlContent = $twig->render('emails/report_warning.html.twig', [
                    'user' => $commentAuthor,
                    'reportCount' => $newReportCount,
                ]);

                $email = (new Email())
                    ->from('tas.sam.se@gmail.com')
                    ->to($commentAuthor->getEmail())
                    ->subject('⚠️ Warning: Your account has been reported multiple times')
                    ->html($htmlContent);

                $mailer->send($email);
            } catch (\Throwable $e) {
                // Log error but don't block the flow
            }
        }

        // At 5+ reports → ban for 2 days
        if ($newReportCount >= 5) {
            $bannedUntil = new \DateTime('+2 days');
            $commentAuthor->setBan(true);
            $commentAuthor->setBannedUntil($bannedUntil);
        }

        $em->flush();

        if ($newReportCount >= 5) {
            $this->addFlash('success', 'User has been banned for 2 days due to excessive reports.');
        } elseif ($newReportCount == 4) {
            $this->addFlash('success', 'Comment reported. A warning email has been sent to the user.');
        } else {
            $this->addFlash('success', 'Comment reported successfully.');
        }

        $referer = $request->headers->get('referer');
        return $referer ? $this->redirect($referer) : $this->redirectToRoute('groups_index');
    }

    #[Route('/posts/{id}/report', name: 'post_report', methods: ['POST'])]
    public function reportPost(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        MailerInterface $mailer,
        Environment $twig
    ): Response {
        // Get current user
        $user = $this->getUser();
        if (!$user) {
            $sessionUserId = $request->getSession()->get('user_id');
            if ($sessionUserId) {
                $user = $em->getRepository(User::class)->find($sessionUserId);
            }
        }
        if (!$user) {
            $this->addFlash('error', 'You must be logged in to report a post.');
            return $this->redirectToRoute('groups_index');
        }

        // Find the post
        $post = $em->getRepository(Posts::class)->find($id);
        if (!$post) {
            $this->addFlash('error', 'Post not found.');
            return $this->redirectToRoute('groups_index');
        }

        // Get post author
        $postAuthor = $post->getAuthorId();

        // Prevent self-reporting
        if ($postAuthor->getId() === $user->getId()) {
            $this->addFlash('error', 'You cannot report your own post.');
            $referer = $request->headers->get('referer');
            return $referer ? $this->redirect($referer) : $this->redirectToRoute('groups_index');
        }

        // Increment report count
        $newReportCount = $postAuthor->getReportNbr() + 1;
        $postAuthor->setReportNbr($newReportCount);

        // At 4 reports → send warning email
        if ($newReportCount == 4) {
            try {
                $htmlContent = $twig->render('emails/report_warning.html.twig', [
                    'user' => $postAuthor,
                    'reportCount' => $newReportCount,
                ]);

                $email = (new Email())
                    ->from('tas.sam.se@gmail.com')
                    ->to($postAuthor->getEmail())
                    ->subject('⚠️ Warning: Your account has been reported multiple times')
                    ->html($htmlContent);

                $mailer->send($email);
            } catch (\Throwable $e) {
                // Log error but don't block the flow
            }
        }

        // At 5+ reports → ban for 2 days
        if ($newReportCount >= 5) {
            $bannedUntil = new \DateTime('+2 days');
            $postAuthor->setBan(true);
            $postAuthor->setBannedUntil($bannedUntil);
        }

        $em->flush();

        if ($newReportCount >= 5) {
            $this->addFlash('success', 'User has been banned for 2 days due to excessive reports.');
        } elseif ($newReportCount == 4) {
            $this->addFlash('success', 'Post reported. A warning email has been sent to the user.');
        } else {
            $this->addFlash('success', 'Post reported successfully.');
        }

        $referer = $request->headers->get('referer');
        return $referer ? $this->redirect($referer) : $this->redirectToRoute('groups_index');
    }

    #[Route('/posts/{id}/delete', name: 'post_delete')]
    public function delete(EntityManagerInterface $em, Posts $post): Response
    {
        $group = $post->getGroupId();

        $reactionRepo = $em->getRepository(Reactions::class);
        $commentRepo = $em->getRepository(Commentaires::class);

        foreach ($reactionRepo->findBy(['post_id' => $post]) as $reaction) {
            $em->remove($reaction);
        }

        foreach ($commentRepo->findBy(['post' => $post]) as $comment) {
            $em->remove($comment);
        }

        $em->remove($post);
        $em->flush();

        if ($group) {
            return $this->redirectToRoute('group_show', ['id' => $group->getId()]);
        }

        return $this->redirectToRoute('groups_index');
    }
    private function resolveCurrentUser(Request $request, EntityManagerInterface $em): ?User
    {
        $authUser = $this->getUser();
        if ($authUser instanceof User) {
            return $authUser;
        }

        $sessionUserId = $request->getSession()->get('user_id');
        if (!$sessionUserId) {
            return null;
        }

        return $em->getRepository(User::class)->find($sessionUserId);
    }

    /**
     * @return array{0: string|null, 1: string|null}
     */
    private function uploadAttachedFile(mixed $uploadedFile): array
    {
        if ($uploadedFile === null) {
            return [null, null];
        }

        $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/posts';
        if (!is_dir($uploadsDir)) {
            @mkdir($uploadsDir, 0775, true);
        }

        $safeName = bin2hex(random_bytes(8));
        $ext = strtolower($uploadedFile->getClientOriginalExtension() ?? '');
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($ext, $allowed, true)) {
            $ext = 'dat';
        }

        $newFilename = $safeName . '.' . $ext;

        try {
            $uploadedFile->move($uploadsDir, $newFilename);
            return [
                $uploadsDir . '/' . $newFilename,
                'uploads/posts/' . $newFilename,
            ];
        } catch (FileException) {
            return [null, null];
        }
    }

    private function redirectAfterPostContext(Request $request, ?Posts $post = null, ?Group $group = null): Response
    {
        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        if ($group) {
            return $this->redirectToRoute('group_show', ['id' => $group->getId()]);
        }

        if ($post?->getGroupId()) {
            return $this->redirectToRoute('group_show', ['id' => $post->getGroupId()->getId()]);
        }

        return $this->redirectToRoute('groups_index');
    }

    private function moderationErrorMessage(string $reason): string
    {
        return match ($reason) {
            'profanity' => 'Blocked by Neutrino: profanity or forbidden words detected.',
            'hate_or_abuse' => 'Blocked by Perspective AI: hate speech, threats, sexual abuse, or severe toxic content detected.',
            'violence' => 'Blocked by YOLOv8 fight detection: violent/fight content detected.',
            'fight' => 'Blocked by YOLOv8 fight detection: violent/fight content detected.',
            'fight_or_violence' => 'Blocked by YOLOv8 fight detection: violent/fight content detected.',
            'fight_moderation_unavailable' => 'Post blocked: fight moderation service unavailable.',
            default => 'Post blocked by moderation.',
        };
    }
}
