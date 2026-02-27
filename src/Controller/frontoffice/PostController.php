<?php

namespace App\Controller\frontoffice;

use App\Entity\Group;
use App\Entity\Reactions;
use App\Entity\Posts;
use App\Entity\Commentaires;
use App\Entity\User;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    #[Route('/groups/{id}/post/new', name: 'group_post_new')]
    public function new(Request $request, EntityManagerInterface $em, Group $group = null): Response
    {
        $user = $this->getUser(); 
        if (!$user) {
            $sessionUserId = $request->getSession()->get('user_id');
            if ($sessionUserId) {
                $user = $em->getRepository(User::class)->find($sessionUserId);
            }
        }
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

            // Link to group if coming from a group page
            if ($group) {
                $post->setGroupId($group);
            }

            $uploadedFile = $form->get('attached_file')->getData();
            if ($uploadedFile) {
                $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/posts';
                if (!is_dir($uploadsDir)) {
                    @mkdir($uploadsDir, 0775, true);
                }
                $safeName = bin2hex(random_bytes(8));
                $ext = strtolower($uploadedFile->getClientOriginalExtension() ?? '');
                $allowed = ['jpg','jpeg','png','gif','webp'];
                if (!in_array($ext, $allowed, true)) {
                    $ext = 'dat';
                }
                $newFilename = $safeName . '.' . $ext;
                try {
                    $uploadedFile->move($uploadsDir, $newFilename);
                    $post->setAttachedFile('uploads/posts/' . $newFilename);
                } catch (FileException $e) {
                    // silently ignore upload error for now
                }
            }

            $em->persist($post);
            $em->flush();

            $this->addFlash('success', 'Post created successfully!');

            if ($group) {
                return $this->redirectToRoute('group_show', ['id' => $group->getId()]);
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
        $user = $this->getUser();
        if (!$user) {
            $sessionUserId = $request->getSession()->get('user_id');
            if ($sessionUserId) {
                $user = $em->getRepository(User::class)->find($sessionUserId);
            }
        }
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

        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }
        if ($post->getGroupId()) {
            return $this->redirectToRoute('group_show', ['id' => $post->getGroupId()->getId()]);
        }
        return $this->redirectToRoute('groups_index');
    }

    #[Route('/posts/{id}/comment', name: 'post_comment', methods: ['POST'])]
    public function comment(Request $request, EntityManagerInterface $em, Posts $post): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $sessionUserId = $request->getSession()->get('user_id');
            if ($sessionUserId) {
                $user = $em->getRepository(User::class)->find($sessionUserId);
            }
        }
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

        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }
        if ($post->getGroupId()) {
            return $this->redirectToRoute('group_show', ['id' => $post->getGroupId()->getId()]);
        }
        return $this->redirectToRoute('groups_index');
    }

    #[Route('/posts/{id}/delete', name: 'post_delete')]
    public function delete(EntityManagerInterface $em, Posts $post): Response
    {
        $group = $post->getGroupId();
        // Remove dependent records first to satisfy FK constraints
        $reactionRepo = $em->getRepository(Reactions::class);
        $commentRepo = $em->getRepository(Commentaires::class);
        foreach ($reactionRepo->findBy(['post_id' => $post]) as $r) {
            $em->remove($r);
        }
        foreach ($commentRepo->findBy(['post' => $post]) as $c) {
            $em->remove($c);
        }
        $em->remove($post);
        $em->flush();
        if ($group) {
            return $this->redirectToRoute('group_show', ['id' => $group->getId()]);
        }
        return $this->redirectToRoute('groups_index');
    }
}
