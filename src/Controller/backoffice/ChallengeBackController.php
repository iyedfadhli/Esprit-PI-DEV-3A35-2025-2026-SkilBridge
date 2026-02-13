<?php
namespace App\Controller\backoffice;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Challenge;
use App\Entity\Activity;
use App\Entity\Evaluation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class ChallengeBackController extends AbstractController
{
    #[Route('/admin/challenge', name: 'challengeback')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        // ALWAYS defined first
        $challenges = $em->getRepository(Challenge::class)->findAll();
        $activities = $em->getRepository(Activity::class)->findAll();
        $evaluations = $em->getRepository(Evaluation::class)->findAll();
        $activeTab = $request->query->get('tab', 'challenge-wrapper');


        $challengeSearch = $request->query->get('challenge_search');
        $challengeSort = $request->query->get('challenge_sort');

        if ($challengeSearch || $challengeSort) {

            $qb = $em->getRepository(Challenge::class)->createQueryBuilder('c');

            if ($challengeSearch) {
                $qb->andWhere('c.id LIKE :search OR c.title LIKE :search')
                    ->setParameter('search', '%' . $challengeSearch . '%');
            }

            if ($challengeSort === 'ID') {
                $qb->orderBy('c.id', 'ASC');
            } elseif ($challengeSort === 'Title') {
                $qb->orderBy('c.title', 'ASC');
            }

            $challenges = $qb->getQuery()->getResult();
        }

        $activitySearch = $request->query->get('activity_search');
        $activitySort = $request->query->get('activity_sort');

        if ($activitySearch || $activitySort) {

            $qb = $em->getRepository(Activity::class)->createQueryBuilder('a');

            if ($activitySearch) {
                $qb->andWhere('a.id LIKE :search OR a.status LIKE :search')
                    ->setParameter('search', '%' . $activitySearch . '%');
            }

            if ($activitySort === 'ID') {
                $qb->orderBy('a.id', 'ASC');
            } elseif ($activitySort === 'Status') {
                $qb->orderBy('a.status', 'ASC');
            }

            $activities = $qb->getQuery()->getResult();
        }

        $evaluationSearch = $request->query->get('evaluation_search');
        $evaluationSort = $request->query->get('evaluation_sort');

        if ($evaluationSearch || $evaluationSort) {

            $qb = $em->getRepository(Evaluation::class)->createQueryBuilder('e');

            if ($evaluationSearch) {
                $qb->andWhere('e.id LIKE :search OR e.groupScore LIKE :search')
                    ->setParameter('search', '%' . $evaluationSearch . '%');
            }

            if ($evaluationSort === 'ID') {
                $qb->orderBy('e.id', 'ASC');
            } elseif ($evaluationSort === 'Group Score') {
                $qb->orderBy('e.groupScore', 'ASC');
            }

            $evaluations = $qb->getQuery()->getResult();
        }


        return $this->render('backoffice/challenge/backoffice_challenge.html.twig', [
            'challenges' => $challenges,
            'activities' => $activities,
            'evaluations' => $evaluations,
            'activeTab' => $activeTab, 

        ]);
    }

    #[Route('/backoffice/challenge/delete/{id}', name: 'backoffice_challenge_delete', methods: ['POST'])]
    public function delete_challenge(EntityManagerInterface $em, int $id): Response
    {
        $challenge = $em->getRepository(Challenge::class)->find($id);

        if (!$challenge) {
            $this->addFlash('error', 'Challenge not found.');
            return $this->redirectToRoute('challengeback'); // your list page route
        }

        $em->remove($challenge);
        $em->flush();

        $this->addFlash('success', 'Challenge deleted successfully.');

        return $this->redirectToRoute('challengeback');
    }
    #[Route('/backoffice/activity/delete/{id}', name: 'backoffice_activity_delete', methods: ['POST'])]
    public function deleteActivity(int $id, EntityManagerInterface $em): Response
    {
        $activity = $em->getRepository(Activity::class)->find($id);

        if (!$activity) {
            $this->addFlash('error', 'Activity not found.');
            return $this->redirectToRoute('challengeback');
        }

        $em->remove($activity);
        $em->flush();
        $this->addFlash('success', 'Activity deleted successfully.');

        return $this->redirectToRoute('challengeback');
    }
    #[Route('/backoffice/evaluation/delete/{id}', name: 'backoffice_evaluation_delete', methods: ['POST'])]
    public function deleteEvaluation(int $id, EntityManagerInterface $em): Response
    {
        $evaluation = $em->getRepository(Evaluation::class)->find($id);

        if (!$evaluation) {
            $this->addFlash('error', 'evaluation not found.');
            return $this->redirectToRoute('challengeback');
        }

        $em->remove($evaluation);
        $em->flush();
        $this->addFlash('success', 'evaluation deleted successfully.');

        return $this->redirectToRoute('challengeback');
    }
    #[Route('/admin/evaluations', name: 'evaluations_list')]
    public function list(Request $request, EntityManagerInterface $em): Response
    {
        $search = $request->query->get('search', '');
        $sortBy = $request->query->get('sort', '');

        $repo = $em->getRepository(Evaluation::class);
        $qb = $repo->createQueryBuilder('e');

        if ($search) {
            $qb->andWhere('e.id LIKE :search OR e.groupScore LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($sortBy === 'ID') {
            $qb->orderBy('e.id', 'ASC');
        } elseif ($sortBy === 'Group Score') {
            $qb->orderBy('e.groupScore', 'ASC');
        }

        $evaluations = $qb->getQuery()->getResult();

        return $this->redirectToRoute('challengeback', [
            'evaluations' => $evaluations,
            'search' => $search,
            'sortBy' => $sortBy,
        ]);
    }
}
