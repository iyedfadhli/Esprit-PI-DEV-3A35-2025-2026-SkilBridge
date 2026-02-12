<?php

namespace App\Controller\frontoffice;

use App\Entity\Group;
use App\Entity\Posts;
use App\Entity\User;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    #[Route('/groups/{id}/post/new', name: 'group_post_new')]
    public function new(Request $request, EntityManagerInterface $em, Group $group = null): Response
    {
        $user = $this->getUser(); 
        // fallback for dev if getUser is null, use dummy user 1
        if (!$user) {
             $user = $em->getRepository(User::class)->find(1);
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
}