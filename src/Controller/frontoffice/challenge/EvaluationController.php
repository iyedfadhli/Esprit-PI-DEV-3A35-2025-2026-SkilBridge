<?php

namespace App\Controller\frontoffice\challenge;

use App\Entity\Challenge;
use App\Entity\User;
use App\Entity\Activity;
use App\Entity\Evaluation;
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

final class EvaluationController extends AbstractController
{

    #[Route('/evaluation', name: 'evaluation')]
    public function showChallenges(EntityManagerInterface $em): Response
    {
        $userId = 2; // hardcoded for now

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
    public function submitProblemSolution(Request $request, EntityManagerInterface $em, int $problemId)
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
    public function submitMemberScore(Request $request, EntityManagerInterface $em, int $memberActivityId)
    {
        $memberActivity = $em->getRepository(MemberActivity::class)->find($memberActivityId);
        if (!$memberActivity) {
            throw $this->createNotFoundException('MemberActivity not found.');
        }

        $score = $request->request->get('score');
        $score = (float) $request->request->get('score');
        if (!is_numeric($request->request->get('score'))) {
            $this->addFlash('error', 'Invalid score value.');
            return $this->redirect($request->headers->get('referer'));
        }
        $score = max(0, min(20, $score));
        $memberActivity->setIndivScore($score); 

        return $this->redirect($request->headers->get('referer'));
    }
    #[Route('/evaluation/submit-group-feedback/{activityId}', name: 'submit_group_feedback', methods: ['POST'])]
    public function submitGroupFeedback(
        int $activityId,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $activity = $em->getRepository(Activity::class)->find($activityId);
        $activity = $em->getRepository(Activity::class)->find($activityId);
        if (!$activity) {
            $this->addFlash('error', 'Activity not found.');
            return $this->redirectToRoute('evaluation');
        }

        $scoreRaw = $request->request->get('groupScore');
        if (!is_numeric($scoreRaw)) {
            $this->addFlash('error', 'Invalid group score.');
            return $this->redirectToRoute('evaluation');
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
                return $this->redirectToRoute('evaluation');
            }
        }

        $evaluation->setGroupScore($score);
        $activity->setStatus('evaluated');
        $em->persist($activity);


        $em->persist($evaluation);
        $em->flush();

        $this->addFlash('success', 'Group feedback submitted successfully.');
        return $this->redirectToRoute('evaluation');
    }

}
