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
    $userId = 1; // hardcoded for now

    $challenges = $em->getRepository(Challenge::class)->findBy(
        ['creator' => $userId],
        ['createdAt' => 'DESC']
    );

    $groupsByChallengeId = [];
    $activitiesByChallengeAndGroup = [];
    $memberActivitiesByActivity = [];
    $problemSolutionsByActivity = [];
    $groupFeedbackByActivity = []; // <-- NEW

    foreach ($challenges as $challenge) {

        $activities = $em->getRepository(Activity::class)
            ->findBy(['idChallenge' => $challenge]);

        foreach ($activities as $activity) {
            $group = $activity->getGroupId();
            if (!$group) continue;

            $groupId = $group->getId();

            // Store unique groups
            $groupsByChallengeId[$challenge->getId()][$groupId] = $group;

            // Store activities by group
            $activitiesByChallengeAndGroup[$challenge->getId()][$groupId][] = $activity;

            // MemberActivity + user info
            $memberActivities = $em->getRepository(MemberActivity::class)
                ->findBy(['id_activity' => $activity]);

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
                ->findBy(['activityId' => $activity]);

$evaluation = $em->getRepository(Evaluation::class)
    ->findOneBy(['activity_id' => $activity]);

if ($evaluation) {
    $groupFeedbackByActivity[$activity->getId()] = [
        'score' => $evaluation->getGroupScore(),
        'pdfFilename' => $evaluation->getFeedback() ? basename($evaluation->getFeedback()) : null
    ];
}

        }

        // Convert groups to array
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
        $problem->setSupervisorSolution($solution); // make sure this field exists in your entity
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
        $memberActivity->setIndivScore($score); // make sure score field exists
        $em->flush();

        return $this->redirect($request->headers->get('referer'));
    }
    #[Route('/evaluation/submit-group-feedback/{activityId}', name: 'submit_group_feedback', methods: ['POST'])]
    public function submitGroupFeedback(
        int $activityId,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        // 1️⃣ Find the activity
        $activity = $em->getRepository(Activity::class)->find($activityId);
        if (!$activity) {
            $this->addFlash('error', 'Activity not found.');
            return $this->redirectToRoute('evaluation');
        }

        $score = $request->request->get('groupScore');

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

        // 5️⃣ Set/update group score
        $evaluation->setGroupScore((float) $score);
        $activity->setStatus('evaluated'); 
        $em->persist($activity);


        // 6️⃣ Save to database
        $em->persist($evaluation);
        $em->flush();

        $this->addFlash('success', 'Group feedback submitted successfully.');
        return $this->redirectToRoute('evaluation');
    }







}