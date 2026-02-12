<?php

namespace App\Controller\frontoffice;

use App\Entity\Group;
use App\Entity\Membership;
use App\Entity\Posts;
use App\Entity\User;
use App\Form\GroupType;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use App\Entity\Commentaires;

class GroupController extends AbstractController
{
#[Route('/groups', name: 'groups_index')]
public function index(EntityManagerInterface $em, Request $request): Response
{
    // Temporary: use dummy user with ID 1
    $user = $em->getRepository(User::class)->find(1);

    // --- SIDEBAR DATA: Groups User is Member of ---
    // Groups created by user
    $ownedGroups = $em->getRepository(Group::class)->findBy(['leaderId' => $user]);

    // Groups where user is a member
    $memberships = $em->getRepository(Membership::class)->findBy(['user_id' => $user]);
    $memberGroups = array_map(fn($m) => $m->getGroupId(), $memberships);

    // Merge owned + member groups for the sidebar
    $myGroups = array_unique(array_merge($ownedGroups, $memberGroups), SORT_REGULAR);

    // --- MAIN FEED DATA: Public Posts ---
    // Server-side sort (PHP/Doctrine) based on query param
    $sort = $request->query->get('sort', 'newest');
    $qb = $em->getRepository(Posts::class)->createQueryBuilder('p')
        ->where('p.visibility = :vis')
        ->setParameter('vis', 'public');
    switch ($sort) {
        case 'popular':
            $qb->orderBy('p.likes_counter', 'DESC');
            break;
        case 'oldest':
            $qb->orderBy('p.created_at', 'ASC');
            break;
        default:
            $qb->orderBy('p.created_at', 'DESC');
            $sort = 'newest';
    }
    $publicPosts = $qb->getQuery()->getResult();
    $publicCommentsByPost = [];
    if (!empty($publicPosts)) {
        $postIds = array_map(fn($p) => $p->getId(), $publicPosts);
        $comments = $em->getRepository(Commentaires::class)->createQueryBuilder('c')
            ->where('c.post IN (:postIds)')
            ->setParameter('postIds', $postIds)
            ->orderBy('c.created_at', 'ASC')
            ->getQuery()
            ->getResult();
        foreach ($comments as $c) {
            $pid = $c->getPost()->getId();
            if (!isset($publicCommentsByPost[$pid])) {
                $publicCommentsByPost[$pid] = [];
            }
            $publicCommentsByPost[$pid][] = $c;
        }
    }

    // Also fetch all groups for the "Discover Groups" or "All Groups" section if needed
    $allGroups = $em->getRepository(Group::class)->findAll();

    // --- QUICK POST FORM (Public / No Group) ---
    $quickPost = new Posts();
    $quickPostForm = $this->createForm(PostType::class, $quickPost);

    $quickPostForm->handleRequest($request);
    if ($quickPostForm->isSubmitted() && $quickPostForm->isValid()) {
        $quickPost->setAuthorId($user);
        $quickPost->setCreatedAt(new \DateTimeImmutable());
        $quickPost->setLikesCounter(0);
        $quickPost->setStatus('published');
        $quickPost->setVisibility('public'); // Main feed posts are public by default
        $quickPost->setGroupId(null); // Explicitly no group
        $uploadedFile = $quickPostForm->get('attached_file')->getData();
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
                $quickPost->setAttachedFile('uploads/posts/' . $newFilename);
            } catch (FileException $e) {
                // ignore upload error
            }
        }

        $em->persist($quickPost);
        $em->flush();

        $this->addFlash('success', 'Public post shared successfully!');

        return $this->redirectToRoute('groups_index');
    }

    return $this->render('frontoffice/groups/grp_index.html.twig', [
        'myGroups' => $myGroups,
        'publicPosts' => $publicPosts,
        'allGroups' => $allGroups,
        'quickPostForm' => $quickPostForm->createView(),
        'currentSort' => $sort,
        'commentsByPost' => $publicCommentsByPost,
    ]);
}

    #[Route('/groups/add', name: 'group_add')]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $group = new Group();
        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Auto-set values
            $group->setCreationDate(new \DateTime());
            // Temporary: assign a dummy user as leader until authentication is ready
$dummyUser = $em->getRepository(User::class)->find(1); // ID of a test user in your DB
$group->setLeaderId($dummyUser);
            $group->setRatingScore(0); // initial score
            $uploadedIcon = $form->get('icon_file')->getData();
            $iconUrl = trim((string) $form->get('icon_url')->getData());
            if ($uploadedIcon) {
                $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/groups';
                if (!is_dir($uploadsDir)) {
                    @mkdir($uploadsDir, 0775, true);
                }
                $safeName = bin2hex(random_bytes(8));
                $ext = strtolower($uploadedIcon->getClientOriginalExtension() ?? '');
                $allowed = ['jpg','jpeg','png','gif','webp'];
                if (!in_array($ext, $allowed, true)) {
                    $ext = 'dat';
                }
                $newFilename = $safeName . '.' . $ext;
                try {
                    $uploadedIcon->move($uploadsDir, $newFilename);
                    $group->setIcon('uploads/groups/' . $newFilename);
                } catch (FileException $e) {
                    // ignore upload error
                }
            } elseif ($iconUrl !== '') {
                $iconUrl = trim($iconUrl);
                if (!preg_match('#^(https?:)?//#i', $iconUrl) && !preg_match('#^data:image/#i', $iconUrl)) {
                    $iconUrl = 'https://' . ltrim($iconUrl, '/');
                }
                $group->setIcon($iconUrl);
            }
            if (!$group->getIcon()) {
                $group->setIcon('assets/images/frontoffice/profile_pic.png');
            }

            $em->persist($group);
            $em->flush();

            // Create membership for the creator as leader
            $membership = new Membership();
            $membership->setUserId($dummyUser);
            $membership->setGroupId($group);
            $membership->setRole('leader');
            $membership->setContributionScore(0);
            $membership->setIsActive(true);

            $em->persist($membership);
            $em->flush();

            $this->addFlash('success', 'Group created successfully!');

            return $this->redirectToRoute('groups_index');
        }

        return $this->render('frontoffice/groups/grp_add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/groups/{id}/edit', name: 'group_edit')]
    public function edit(Request $request, Group $group, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedIcon = $form->get('icon_file')->getData();
            $iconUrl = trim((string) $form->get('icon_url')->getData());
            if ($uploadedIcon) {
                $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/groups';
                if (!is_dir($uploadsDir)) {
                    @mkdir($uploadsDir, 0775, true);
                }
                $safeName = bin2hex(random_bytes(8));
                $ext = strtolower($uploadedIcon->getClientOriginalExtension() ?? '');
                $allowed = ['jpg','jpeg','png','gif','webp'];
                if (!in_array($ext, $allowed, true)) {
                    $ext = 'dat';
                }
                $newFilename = $safeName . '.' . $ext;
                try {
                    $uploadedIcon->move($uploadsDir, $newFilename);
                    $group->setIcon('uploads/groups/' . $newFilename);
                } catch (FileException $e) {
                    // ignore upload error
                }
            } elseif ($iconUrl !== '') {
                $iconUrl = trim($iconUrl);
                if (!preg_match('#^(https?:)?//#i', $iconUrl) && !preg_match('#^data:image/#i', $iconUrl)) {
                    $iconUrl = 'https://' . ltrim($iconUrl, '/');
                }
                $group->setIcon($iconUrl);
            }
            $em->flush();
            return $this->redirectToRoute('groups_index');
        }

        return $this->render('frontoffice/groups/grp_edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }



#[Route('/groups/{id}', name: 'group_show')]
public function show(Group $group, Request $request, EntityManagerInterface $em): Response
{
    // User Context
    $user = $em->getRepository(User::class)->find(1); // Dummy user

    // --- SIDEBAR DATA (Same as index) ---
    $ownedGroups = $em->getRepository(Group::class)->findBy(['leaderId' => $user]);
    $memberships = $em->getRepository(Membership::class)->findBy(['user_id' => $user]);
    $memberGroups = array_map(fn($m) => $m->getGroupId(), $memberships);
    $myGroups = array_unique(array_merge($ownedGroups, $memberGroups), SORT_REGULAR);

    // --- GROUP DATA ---
    $groupMemberships = $em->getRepository(Membership::class)->findBy(['group_id' => $group]);
    
    // Fetch posts for this group
    $posts = $em->getRepository(Posts::class)->findBy(['group_id' => $group], ['created_at' => 'DESC']);
    // Fetch comments for these posts
    $commentsByPost = [];
    if (!empty($posts)) {
        $postIds = array_map(fn($p) => $p->getId(), $posts);
        $comments = $em->getRepository(Commentaires::class)->createQueryBuilder('c')
            ->where('c.post IN (:postIds)')
            ->setParameter('postIds', $postIds)
            ->orderBy('c.created_at', 'ASC')
            ->getQuery()
            ->getResult();
        foreach ($comments as $c) {
            $pid = $c->getPost()->getId();
            if (!isset($commentsByPost[$pid])) {
                $commentsByPost[$pid] = [];
            }
            $commentsByPost[$pid][] = $c;
        }
    }

    // Check current user's membership
    $currentUserMembership = $em->getRepository(Membership::class)->findOneBy([
        'user_id' => $user,
        'group_id' => $group
    ]);

    // Handle search
    $searchTerm = $request->query->get('q');
    $users = [];
    if ($searchTerm) {
        $users = $em->getRepository(User::class)->createQueryBuilder('u')
            ->where('LOWER(u.nom) LIKE LOWER(:search)')
            ->orWhere('LOWER(u.prenom) LIKE LOWER(:search)')
            ->orWhere('LOWER(u.email) LIKE LOWER(:search)')
            ->setParameter('search', '%' . $searchTerm . '%')
            ->getQuery()
            ->getResult();
    }

    // Handle "add member" action
    $userId = $request->query->get('userId');
    if ($userId) {
        $user = $em->getRepository(User::class)->find($userId);

        if ($user) {
            try {
                // Check if membership already exists
                $existing = $em->getRepository(Membership::class)->createQueryBuilder('m')
                    ->where('m.group_id = :group')
                    ->andWhere('m.user_id = :user')
                    ->setParameter('group', $group)
                    ->setParameter('user', $user)
                    ->getQuery()
                    ->getOneOrNullResult();

                if ($existing) {
                    $this->addFlash('warning', $user->getPrenom().' '.$user->getNom().' is already a member of this group.');
                } else {
                    $membership = new Membership();
                    $membership->setGroupId($group);
                    $membership->setUserId($user);
                    $membership->setRole('member'); // default role
                    $membership->setContributionScore(0);
                    $membership->setIsActive(true);

                    $em->persist($membership);
                    $em->flush();

                    $this->addFlash('success', $user->getPrenom().' '.$user->getNom().' has been added to the group!');
                }
            } catch (UniqueConstraintViolationException $e) {
                // Database blocked duplicate → show small message instead of error page
                $this->addFlash('warning', $user->getPrenom().' '.$user->getNom().' is already a member of this group.');
            }

            return $this->redirectToRoute('group_show', ['id' => $group->getId()]);
        }
    }

    return $this->render('frontoffice/groups/grp_show.html.twig', [
        'group' => $group,
        'memberships' => $groupMemberships,
        'posts' => $posts,
        'commentsByPost' => $commentsByPost,
        'myGroups' => $myGroups, // Pass sidebar data
        'currentUserMembership' => $currentUserMembership,
        'users' => $users,
    ]);
}

    #[Route('/groups/{id}/delete', name: 'group_delete')]
    public function delete(Group $group, EntityManagerInterface $em): Response
    {
        $em->remove($group);
        $em->flush();

        return $this->redirectToRoute('groups_index');
    }
}
