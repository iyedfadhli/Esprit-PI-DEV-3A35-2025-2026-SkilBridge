<?php

namespace App\Controller\frontoffice\challenge;
use App\Entity\Challenge;
use App\Entity\Activity;
use App\Entity\Group;
use App\Entity\Membership;
use App\Entity\ProblemSolution;
use App\Entity\Evaluation;

use App\Entity\MemberActivity;
use App\Entity\User;

use App\Repository\GroupRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

final class ActivityEvaluationController extends AbstractController
{
    #[Route('/old_activities', name: 'old_activities')]
    public function myOldActivities(Request $request, EntityManagerInterface $em): Response
    {
        $userId = $request->getSession()->get('user_id');
        if (!$userId) {
            return $this->redirectToRoute('sign');
        }
        $user = $em->getRepository(User::class)->find($userId);
        $groupFeedbackByActivity = [];
        $indivScore = [];

        $activities = $em->getRepository(Activity::class)->findByUserMemberships($user->getId());
        $problemSolutionsByActivity = [];

        foreach ($activities as $activity) {
            $problemSolutionsByActivity[$activity->getId()] = $em->getRepository(ProblemSolution::class)
                ->findByActivity($activity);
            $evaluation = $em->getRepository(Evaluation::class)
                ->findOneBy(['activity_id' => $activity]);
            if ($evaluation) {
                $groupFeedbackByActivity[$activity->getId()] = [
                    'score' => $evaluation->getGroupScore(),
                    'pdfFilename' => $evaluation->getFeedback() ? basename($evaluation->getFeedback()) : null,
                    'preFeedback' => $evaluation->getPreFeedback() ?: null
                ];
                $memberActivity = $em->getRepository(MemberActivity::class)
                    ->findOneByActivityAndUser($activity, $user);
                if ($memberActivity) {
                    $indivScore[$activity->getId()] =
                        $memberActivity->getIndivScore();
                }
            }
        }

        return $this->render('frontoffice/challenge/check_old_activity.html.twig', [
            'activities' => $activities,
            'problemSolutions' => $problemSolutionsByActivity,
            'groupFeedback' => $groupFeedbackByActivity,
            'indivScore' => $indivScore

        ]);
    }
    

    #[Route('/old_activities/{activityId}/evaluation', name: 'old_activity_evaluation', requirements: ['activityId' => '\d+'])]
    public function viewEvaluation(int $activityId, Request $request, EntityManagerInterface $em): Response
    {
        $userId = $request->getSession()->get('user_id');
        if (!$userId) {
            return $this->redirectToRoute('sign');
        }
        $activity = $em->getRepository(Activity::class)->find($activityId);
        if (!$activity) {
            throw $this->createNotFoundException('Activity not found.');
        }
        $challenge = $activity->getIdChallenge();

        $problems = $em->getRepository(ProblemSolution::class)->findByActivity($activity);
        $evaluation = $em->getRepository(Evaluation::class)->findOneBy(['activity_id' => $activity]);

        $indivScore = null;
        $user = $em->getRepository(User::class)->find($userId);
        $memberActivity = $em->getRepository(MemberActivity::class)->findOneByActivityAndUser($activity, $user);
        if ($memberActivity) {
            $indivScore = $memberActivity->getIndivScore();
        }

        $feedback = null;
        if ($evaluation) {
            $feedback = [
                'score' => $evaluation->getGroupScore(),
                'pdfFilename' => $evaluation->getFeedback() ? basename($evaluation->getFeedback()) : null
            ];
        }

        $topRankers = [];
        $currentRank = null;
        $totalPassed = 0;
        $entries = [];
        $activitiesOfChallenge = $em->getRepository(Activity::class)->findByChallenge($challenge);
        foreach ($activitiesOfChallenge as $act) {
            $evalAct = $em->getRepository(Evaluation::class)->findOneBy(['activity_id' => $act]);
            if (!$evalAct) { continue; }
            $groupScoreAct = $evalAct->getGroupScore();
            $members = $em->getRepository(MemberActivity::class)->findByActivity($act);
            foreach ($members as $ma) {
                $indiv = $ma->getIndivScore();
                if ($indiv === null) { continue; }
                $final = ($indiv * 0.7) + ($groupScoreAct * 0.3);
                $userEntity = $ma->getUserId();
                $uid = $userEntity ? $userEntity->getId() : null;
                if ($uid === null) { continue; }
                $prenom = $userEntity->getPrenom() ?? '';
                $nom = $userEntity->getNom() ?? '';
                $fullName = trim($prenom . ' ' . $nom);
                $photoRaw = $userEntity->getPhoto();
                $photo = null;
                if ($photoRaw) {
                    $photo = basename(str_replace('\\','/',$photoRaw));
                }
                if (!isset($entries[$uid]) || $final > $entries[$uid]['final']) {
                    $entries[$uid] = [
                        'userId' => $uid,
                        'userName' => $fullName !== '' ? $fullName : ($userEntity->getEmail() ?? 'Unknown'),
                        'photo' => $photo,
                        'final' => round($final, 2)
                    ];
                }
            }
        }
        $entries = array_values($entries);
        usort($entries, static function ($a, $b) { return $b['final'] <=> $a['final']; });
        $totalPassed = count($entries);
        $topRankers = array_slice($entries, 0, 3);
        foreach ($entries as $idx => $e) {
            if ($e['userId'] === $userId) {
                $currentRank = $idx + 1;
                break;
            }
        }

        return $this->render('frontoffice/challenge/old_activity_evaluation.html.twig', [
            'challenge' => $challenge,
            'activity' => $activity,
            'problems' => $problems,
            'feedback' => $feedback,
            'indivScore' => $indivScore,
            'topRankers' => $topRankers,
            'currentRank' => $currentRank,
            'totalPassed' => $totalPassed,
            'leaderboard' => $entries,
            'viewerUserId' => $userId,
        ]);
    }

}
