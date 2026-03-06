<?php

namespace App\Controller\frontoffice\challenge;

use App\Entity\Challenge;
use App\Entity\User;
use App\Entity\Activity;
use App\Entity\Evaluation;
use App\Entity\Group;
use App\Entity\MemberActivity;

use App\Entity\ProblemSolution;
use App\Form\ChallengeType;
use App\Form\ChallengeEditType;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Dompdf\Dompdf;
use Dompdf\Options;

final class EvaluationController extends AbstractController
{

    #[Route('/evaluation', name: 'evaluation')]
    public function showChallenges(Request $request, EntityManagerInterface $em): Response
    {
        $userId = $request->getSession()->get('user_id');
        if (!$userId) {
            return $this->redirectToRoute('sign');
        }

        $challenges = $em->getRepository(Challenge::class)->findByCreatorIdOrderedDesc($userId);

        $groupsByChallengeId = [];
        $activitiesByChallengeAndGroup = [];
        $memberActivitiesByActivity = [];
        $problemSolutionsByActivity = [];
        $groupFeedbackByActivity = []; 

        foreach ($challenges as $challenge) {

            $activities = $em->getRepository(Activity::class)->findByChallenge($challenge);

            foreach ($activities as $activity) {
                $group = $activity->getGroupId();
                if (!$group)
                    continue;

                $groupId = $group->getId();

                $groupsByChallengeId[$challenge->getId()][$groupId] = $group;

                $activitiesByChallengeAndGroup[$challenge->getId()][$groupId][] = $activity;

                $memberActivities = $em->getRepository(MemberActivity::class)
                    ->findByActivity($activity);

                $membersWithUsers = [];
                foreach ($memberActivities as $member) {
                    $user = $em->getRepository(User::class)->find($member->getUserId());
                    $membersWithUsers[] = [
                        'member' => $member,
                        'user' => $user
                    ];
                }
                $memberActivitiesByActivity[$activity->getId()] = $membersWithUsers;

                $problemSolutionsByActivity[$activity->getId()] = $em->getRepository(ProblemSolution::class)
                    ->findByActivity($activity);

                $evaluation = $em->getRepository(Evaluation::class)
                    ->findOneBy(['activity_id' => $activity]);

                if ($evaluation) {
                    $groupFeedbackByActivity[$activity->getId()] = [
                        'score' => $evaluation->getGroupScore(),
                        'pdfFilename' => $evaluation->getFeedback() ? basename($evaluation->getFeedback()) : null
                    ];
                }

            }

            if (isset($groupsByChallengeId[$challenge->getId()])) {
                $groupsByChallengeId[$challenge->getId()] = array_values($groupsByChallengeId[$challenge->getId()]);
            } else {
                $groupsByChallengeId[$challenge->getId()] = [];
            }
        }

        return $this->render('frontoffice/challenge/evaluation.html.twig', [
            'challenges' => $challenges,
            'groups' => $groupsByChallengeId,
            'activitiesByChallengeAndGroup' => $activitiesByChallengeAndGroup,
            'memberActivitiesByActivity' => $memberActivitiesByActivity,
            'problemSolutionsByActivity' => $problemSolutionsByActivity,
            'groupFeedback' => $groupFeedbackByActivity,
        ]);
    }


    #[Route('/submit_problem_solution/{problemId}', name: 'submit_problem_solution', methods: ['POST'])]
    public function submitProblemSolution(Request $request, EntityManagerInterface $em, int $problemId): Response
    {
        $problem = $em->getRepository(ProblemSolution::class)->find($problemId);
        if (!$problem) {
            throw $this->createNotFoundException('Problem not found.');
        }

        $solution = $request->request->get('supervisorSolution');
        $problem->setSupervisorSolution($solution); 
        $em->flush();

        return $this->redirect($request->headers->get('referer'));
    }


    #[Route('/submit_member_score/{memberActivityId}', name: 'submit_member_score', methods: ['POST'])]
    public function submitMemberScore(Request $request, EntityManagerInterface $em, int $memberActivityId): Response
    {
        $memberActivity = $em->getRepository(MemberActivity::class)->find($memberActivityId);
        if (!$memberActivity) {
            throw $this->createNotFoundException('MemberActivity not found.');
        }

        $score = (float) $request->request->get('score');
        if (!is_numeric($request->request->get('score'))) {
            $this->addFlash('error', 'Invalid score value.');
            return $this->redirect($request->headers->get('referer'));
        }
        $score = max(0, min(20, $score));
        $memberActivity->setIndivScore($score); 
        $em->flush();
        $this->addFlash('success', 'Individual score saved.');

        return $this->redirect($request->headers->get('referer'));
    }
    #[Route('/evaluation/submit-group-feedback/{activityId}', name: 'submit_group_feedback', methods: ['POST'])]
    public function submitGroupFeedback(
        int $activityId,
        Request $request,
        EntityManagerInterface $em,
        \App\Service\NotificationService $notifier
    ): Response {
        $activity = $em->getRepository(Activity::class)->find($activityId);
        if (!$activity) {
            $this->addFlash('error', 'Activity not found.');
            return $this->redirectToRoute('evaluation');
        }

        $challenge = $activity->getIdChallenge();
        $group = $activity->getGroupId();

        $scoreRaw = $request->request->get('groupScore');
        if (!is_numeric($scoreRaw)) {
            $this->addFlash('error', 'Invalid group score.');
            return $this->redirectToRoute('evaluation_group_activities', [
                'challengeId' => $challenge->getId(),
                'groupId' => $group ? $group->getId() : 0
            ]);
        }
        $score = max(0, min(20, (float) $scoreRaw));

        $evaluation = $em->getRepository(Evaluation::class)->findOneBy(['activity_id' => $activity]);
        if (!$evaluation) {
            $evaluation = new Evaluation();
            $evaluation->setActivityId($activity);
        }

        $file = $request->files->get('feedbackPdf');
        if ($file && $file->getSize() > 0) {
            $originalFilename = $file->getClientOriginalName();
            $safeFilename = preg_replace('/[^a-zA-Z0-9-_]/', '_', pathinfo($originalFilename, PATHINFO_FILENAME));
            $extension = $file->getClientOriginalExtension();
            $filename = $safeFilename . '.' . $extension;

            $targetDir = $this->getParameter('CHALLENGES_UPLOAD_DIR');


            try {
                $file->move($targetDir, $filename);
                $evaluation->setFeedback('assets/challenge/pdf/' . $filename);
            } catch (FileException $e) {
                $this->addFlash('error', 'Failed to upload PDF: ' . $e->getMessage());
                return $this->redirectToRoute('evaluation_group_activities', [
                    'challengeId' => $challenge->getId(),
                    'groupId' => $group ? $group->getId() : 0
                ]);
            }
        }

        $evaluation->setGroupScore($score);
        $activity->setStatus('evaluated');
        $em->persist($activity);

        $em->persist($evaluation);
        $em->flush();

        // already set above
        if ($group) {
            $memberships = $em->getRepository(\App\Entity\Membership::class)->findBy(['group_id' => $group]);
            foreach ($memberships as $membership) {
                $member = $membership->getUserId();
                if ($member) {
                    $message = sprintf('View the Result of your activity for Challenge %s', (string) $challenge->getTitle());
                    $notifier->notify($member, $message, [
                        'challengeId' => $challenge->getId(),
                        'groupId' => $group->getId(),
                        'activityId' => $activity->getId(),
                        'type' => 'activity_evaluated'
                    ]);
                }
            }
        }

        $this->addFlash('success', 'Group feedback submitted successfully.');
        return $this->redirectToRoute('evaluation_group_activities', [
            'challengeId' => $challenge->getId(),
            'groupId' => $group ? $group->getId() : 0
        ]);
    }

    #[Route('/evaluation/challenge/{challengeId}/group/{groupId}', name: 'evaluation_group_activities')]
    public function viewGroupActivities(
        int $challengeId,
        int $groupId,
        EntityManagerInterface $em
    ): Response {
        $challenge = $em->getRepository(Challenge::class)->find($challengeId);
        $group = $em->getRepository(Group::class)->find($groupId);
        if (!$challenge || !$group) {
            throw $this->createNotFoundException('Challenge or Group not found.');
        }

        $activities = $em->getRepository(Activity::class)->findBy([
            'idChallenge' => $challenge,
            'group_id' => $group
        ]);

        $memberActivitiesByActivity = [];
        $problemSolutionsByActivity = [];
        $groupFeedbackByActivity = [];

        foreach ($activities as $activity) {
            $members = $em->getRepository(MemberActivity::class)->findByActivity($activity);
            $membersWithUsers = [];
            foreach ($members as $member) {
                $user = $em->getRepository(User::class)->find($member->getUserId());
                $membersWithUsers[] = ['member' => $member, 'user' => $user];
            }
            $memberActivitiesByActivity[$activity->getId()] = $membersWithUsers;

            $problemSolutionsByActivity[$activity->getId()] = $em->getRepository(ProblemSolution::class)
                ->findByActivity($activity);

            $evaluation = $em->getRepository(Evaluation::class)->findOneBy(['activity_id' => $activity]);
            if ($evaluation) {
                $groupFeedbackByActivity[$activity->getId()] = [
                    'score' => $evaluation->getGroupScore(),
                    'pdfFilename' => $evaluation->getFeedback() ? basename($evaluation->getFeedback()) : null
                ];
            }
        }

        return $this->render('frontoffice/challenge/evaluation_group_activities.html.twig', [
            'challenge' => $challenge,
            'group' => $group,
            'activities' => $activities,
            'memberActivitiesByActivity' => $memberActivitiesByActivity,
            'problemSolutionsByActivity' => $problemSolutionsByActivity,
            'groupFeedback' => $groupFeedbackByActivity,
        ]);
    }

    #[Route('/certificate/challenge/{challengeId}/activity/{activityId}/user/{userId}', name: 'challenge_certificate_preview', methods: ['GET'])]
    public function previewCertificate(int $challengeId, int $activityId, int $userId, EntityManagerInterface $em): Response
    {
        $challenge = $em->getRepository(Challenge::class)->find($challengeId);
        $activity = $em->getRepository(Activity::class)->find($activityId);
        $user = $em->getRepository(User::class)->find($userId);
        if (!$challenge || !$activity || !$user) {
            throw $this->createNotFoundException('Data not found');
        }
        $evaluation = $em->getRepository(Evaluation::class)->findOneBy(['activity_id' => $activity]);
        $groupScore = $evaluation ? (float) $evaluation->getGroupScore() : null;
        $memberActivity = $em->getRepository(\App\Entity\MemberActivity::class)->findOneByActivityAndUser($activity, $user);
        $indivScore = $memberActivity ? $memberActivity->getIndivScore() : null;
        $score = ($groupScore !== null && $indivScore !== null) ? round(($indivScore * 0.7) + ($groupScore * 0.3), 2) : 0.0;
        if ($score < 10) {
            return new Response('Certificate available only for passed activities', 403);
        }
        return $this->render('frontoffice/challenge/certificate.html.twig', [
            'user' => $user,
            'challenge' => $challenge,
            'activity' => $activity,
            'score' => $score,
            'issueDate' => new \DateTime(),
            'certNumber' => sprintf('CH-%s-%s-%s', (string) $challenge->getId(), (string) $activity->getId(), (string) $user->getId()),
        ]);
    }

    #[Route('/certificate/challenge/{challengeId}/activity/{activityId}/user/{userId}/pdf', name: 'challenge_certificate_pdf', methods: ['GET'])]
    public function downloadCertificatePdf(int $challengeId, int $activityId, int $userId, EntityManagerInterface $em): Response
    {
        $challenge = $em->getRepository(Challenge::class)->find($challengeId);
        $activity = $em->getRepository(Activity::class)->find($activityId);
        $user = $em->getRepository(User::class)->find($userId);
        if (!$challenge || !$activity || !$user) {
            throw $this->createNotFoundException('Data not found');
        }
        $evaluation = $em->getRepository(Evaluation::class)->findOneBy(['activity_id' => $activity]);
        $groupScore = $evaluation ? (float) $evaluation->getGroupScore() : null;
        $memberActivity = $em->getRepository(\App\Entity\MemberActivity::class)->findOneByActivityAndUser($activity, $user);
        $indivScore = $memberActivity ? $memberActivity->getIndivScore() : null;
        $score = ($groupScore !== null && $indivScore !== null) ? round(($indivScore * 0.7) + ($groupScore * 0.3), 2) : 0.0;
        if ($score < 10) {
            return new Response('Certificate available only for passed activities', 403);
        }
        $html = $this->renderView('frontoffice/challenge/certificate.html.twig', [
            'user' => $user,
            'challenge' => $challenge,
            'activity' => $activity,
            'score' => $score,
            'issueDate' => new \DateTime(),
            'certNumber' => sprintf('CH-%s-%s-%s', (string) $challenge->getId(), (string) $activity->getId(), (string) $user->getId()),
            'pdf' => true,
        ]);
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $pdfContent = $dompdf->output();
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . sprintf('certificate_ch%s_user%s.pdf', (string) $challenge->getId(), (string) $user->getId()) . '"',
        ]);
    }

}
