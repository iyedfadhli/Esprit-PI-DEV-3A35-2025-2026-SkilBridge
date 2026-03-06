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
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

final class ChallengeController extends AbstractController
{
    #[Route('supervisor_challenge', name: 'supervisor_challenge')]
    public function new(Request $request, EntityManagerInterface $em, HttpClientInterface $http): Response
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
            if ($creator !== null) {
                $challenge->assignCreator($creator);
            }
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

            $pdfPublicPath = $challenge->getContent();
            if ($pdfPublicPath) {
                $filenameBase = basename($pdfPublicPath);
                $uploadDir = rtrim((string) $this->getParameter('CHALLENGES_UPLOAD_DIR'), "/\\");
                $uploadAbs = $uploadDir . DIRECTORY_SEPARATOR . $filenameBase;
                $publicAbs = $this->getParameter('kernel.project_dir') . '/public/' . ltrim($pdfPublicPath, '/');
                $existingAbs = null;
                if (file_exists($uploadAbs)) {
                    $existingAbs = $uploadAbs;
                } elseif (file_exists($publicAbs)) {
                    $existingAbs = $publicAbs;
                }
                if ($existingAbs && is_readable($existingAbs)) {
                    $formData = new FormDataPart([
                        'files' => DataPart::fromPath($existingAbs, $filenameBase, 'application/pdf'),
                        'metadata' => json_encode(['challenge_id' => (string) $challenge->getId()]),
                        'additionalMetadata' => json_encode(['challenge_id' => (string) $challenge->getId()]),
                        'returnList' => 'true',
                    ]);

                    $headers = $formData->getPreparedHeaders()->toArray();

                    try {
                        $response = $http->request('POST', 'http://localhost:3000/api/v1/vector/upsert/67c98e5a-c5d6-4cd3-ac0b-ecf7f5564455', [
                            'headers' => $headers,
                            'body' => $formData->bodyToString(),
                        ]);
                        $status = $response->getStatusCode();
                        $content = $response->getContent(false);
                        if ($status !== 200 && $status !== 201) {
                            $this->addFlash('error', 'Flowise Error: ' . $content);
                        } else {
                            $this->addFlash('success', $content);
                        }
                    } catch (\Throwable $e) {
                        $this->addFlash('error', 'Flowise Error: ' . $e->getMessage());
                    }
                }
            }

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
            'groupsByChallenge' => array_map(function ($arr) {
                return array_values($arr);
            }, $groupsByChallenge),
        ]);
    }
    #[Route('/supervisor_challenge/{id}/edit', name: 'supervisor_challenge_edit', methods: ['POST'])]
    public function edit(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $challenge = $em->getRepository(Challenge::class)->find($id);
        if (!$challenge) {
            $this->addFlash('error', 'Challenge not found.');
            return $this->redirectToRoute('supervisor_challenge');
        }
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
    public function delete(Request $request, int $id, EntityManagerInterface $em): Response
    {
        $challenge = $em->getRepository(Challenge::class)->find($id);
        if (!$challenge) {
            $this->addFlash('error', 'Challenge not found.');
            return $this->redirectToRoute('supervisor_challenge');
        }
        if ($this->isCsrfTokenValid('delete' . $challenge->getId(), $request->request->get('_token'))) {
            $em->remove($challenge);
            $em->flush();
            $this->addFlash('success', 'Challenge deleted successfully!');
        }

        return $this->redirectToRoute('supervisor_challenge');
    }





}
