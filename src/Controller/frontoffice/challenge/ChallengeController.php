<?php

namespace App\Controller\frontoffice\challenge;

use App\Entity\Challenge;
use App\Entity\User;
use App\Entity\Course;
use App\Form\ChallengeType;
use App\Form\ChallengeEditType;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ChallengeController extends AbstractController
{
    #[Route('supervisor_challenge', name: 'supervisor_challenge')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $challenge = new Challenge();
        $formA = $this->createForm(ChallengeType::class, $challenge);
        $formA->handleRequest($request);

        if ($formA->isSubmitted() && $formA->isValid()) {
            $creatorId = $request->getSession()->get('user_id');
            if (!$creatorId) {
                return $this->redirectToRoute('sign');
            }
            $creator = $em->getRepository(User::class)->find($creatorId);
            $course = $em->getRepository(Course::class)->findOneBy([]);
            if (!$course) {
                $this->addFlash('error', 'No course available.');
                return $this->redirectToRoute('supervisor_challenge');
            }
            $challenge->setCreator($creator);
            $challenge->setCourse($course);
            $challenge->setCreatedAt(new \DateTime());
            $descriptionText = $formA->get('descriptionText')->getData();
            if ($descriptionText) {
                $challenge->setDescription($descriptionText);
            }

            $contentFile = $formA->get('contentFile')->getData();
            if ($contentFile) {
                $originalExtension = $contentFile->getClientOriginalExtension();
                $safeTitle = preg_replace('/[^a-zA-Z0-9-_]/', '_', $challenge->getTitle());
                $filename = $safeTitle . '.' . $originalExtension;
                $targetDir = $this->getParameter('CHALLENGES_UPLOAD_DIR');
                $contentFile->move($targetDir, $filename);

                $challenge->setContent('assets/challenge/pdf/' . $filename);
            }

            $em->persist($challenge);
            $em->flush();

            $this->addFlash('success', 'Challenge created successfully!');
            return $this->redirectToRoute('supervisor_challenge');
        }
        $viewerId = $request->getSession()->get('user_id');
        if (!$viewerId) {
            return $this->redirectToRoute('sign');
        }
        $viewer = $em->getRepository(User::class)->find($viewerId);
        $userChallenges = $em->getRepository(Challenge::class)
            ->findBy(['creator' => $viewer], ['createdAt' => 'DESC']);

        $editForms = [];
        foreach ($userChallenges as $challenge) {
            $form = $this->createForm(ChallengeEditType::class, $challenge, [
                'action' => $this->generateUrl('supervisor_challenge_edit', ['id' => $challenge->getId()]),
                'method' => 'POST'
            ]);
            if ($request->isMethod('POST') && $request->request->has($form->getName())) {
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $file = $form->get('content')->getData();
                    if ($file) {
                        $originalExtension = $file->getClientOriginalExtension();
                        $safeTitle = preg_replace('/[^a-zA-Z0-9-_]/', '_', $challenge->getTitle());
                        $filename = $safeTitle . '.' . $originalExtension;
                        $file->move($this->getParameter('CHALLENGES_UPLOAD_DIR'), $filename);
                        $challenge->setContent('assets/challenge/pdf/' . $filename);
                    }

                    $em->flush();
                    $this->addFlash('success', 'Challenge updated successfully!');
                    return $this->redirectToRoute('supervisor_challenge');
                }
            }

            $editForms[$challenge->getId()] = $form->createView();
        }

        $groupsWorked = [];
        $groupsByChallenge = [];
        foreach ($userChallenges as $ch) {
            $activities = $em->getRepository(\App\Entity\Activity::class)->findByChallenge($ch);
            $uniqueGroupIds = [];
            foreach ($activities as $a) {
                $g = $a->getGroupId();
                if ($g) {
                    $uniqueGroupIds[$g->getId()] = true;
                    $groupsByChallenge[$ch->getId()][$g->getId()] = $g;
                }
            }
            $groupsWorked[$ch->getId()] = count($uniqueGroupIds);
        }

        return $this->render('frontoffice/challenge/challenge.html.twig', [
            'challenges' => $userChallenges,
            'formA' => $formA->createView(),
            'editForms' => $editForms,
            'groupsWorked' => $groupsWorked,
            'groupsByChallenge' => array_map(function($arr){ return array_values($arr); }, $groupsByChallenge),
        ]);
    }
    #[Route('/supervisor_challenge/{id}/edit', name: 'supervisor_challenge_edit', methods: ['POST'])]
    public function edit(Challenge $challenge, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ChallengeEditType::class, $challenge);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('content')->getData();

            if ($file) {
                $oldFile = $challenge->getContent();
                if ($oldFile && file_exists($this->getParameter('kernel.project_dir') . '/public/' . $oldFile)) {
                    unlink($this->getParameter('kernel.project_dir') . '/public/' . $oldFile);
                }
                $originalExtension = $file->getClientOriginalExtension();
                $safeTitle = preg_replace('/[^a-zA-Z0-9-_]/', '_', $challenge->getTitle());
                $filename = $safeTitle . '.' . $originalExtension;
                $file->move($this->getParameter('CHALLENGES_UPLOAD_DIR'), $filename);
                $challenge->setContent('assets/challenge/pdf/' . $filename);
            }

            $em->flush();
            $this->addFlash('success', 'Challenge updated successfully!');
        }

        return $this->redirectToRoute('supervisor_challenge');
    }
    #[Route('/supervisor_challenge/{id}/delete', name: 'challenge_delete', methods: ['POST'])]
    public function delete(Request $request, Challenge $challenge, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $challenge->getId(), $request->request->get('_token'))) {
            $em->remove($challenge);
            $em->flush();
            $this->addFlash('success', 'Challenge deleted successfully!');
        }

        return $this->redirectToRoute('supervisor_challenge');
    }





}


