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
    public function myOldActivities(EntityManagerInterface $em): Response
    {
        $user = 2;
        $groupFeedbackByActivity = [];
        $indivScore = [];

        $activities = $em->getRepository(Activity::class)->findByUserMemberships($user);
        $problemSolutionsByActivity = [];

        foreach ($activities as $activity) {
            $problemSolutionsByActivity[$activity->getId()] = $em->getRepository(ProblemSolution::class)
                ->findByActivity($activity);
            $evaluation = $em->getRepository(Evaluation::class)
                ->findOneBy(['activity_id' => $activity]);
            if ($evaluation) {
                $groupFeedbackByActivity[$activity->getId()] = [
                    'score' => $evaluation->getGroupScore(),
                    'pdfFilename' => $evaluation->getFeedback() ? basename($evaluation->getFeedback()) : null
                ];
                $memberActivity = $em->getRepository(MemberActivity::class)
                    ->findOneBy([
                        'id_activity' => $activity,
                        'user_id' => $user
                    ]);
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
    



}
