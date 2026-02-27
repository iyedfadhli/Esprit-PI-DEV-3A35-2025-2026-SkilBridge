<?php

namespace App\Controller\frontoffice;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class NotificationController extends AbstractController
{
    #[Route('/notifications', name: 'notifications_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $em)
    {
        $userId = $request->getSession()->get('user_id');
        if (!$userId) {
            return $this->redirectToRoute('sign');
        }
        $user = $em->getRepository(User::class)->find($userId);
        if (!$user) {
            return $this->redirectToRoute('sign');
        }

        $page = max(1, (int) $request->query->get('page', 1));
        $pageSize = max(1, min(50, (int) $request->query->get('limit', 10)));

        $qb = $em->getRepository(Notification::class)->createQueryBuilder('n')
            ->andWhere('n.owner = :owner')
            ->setParameter('owner', $user)
            ->orderBy('n.CreatedAt', 'DESC');

        $countQb = clone $qb;
        $total = (int) $countQb->select('COUNT(n.id)')->getQuery()->getSingleScalarResult();
        $pagesCount = (int) ceil($total / $pageSize);
        $page = min($page, max(1, $pagesCount));

        $list = $qb->setFirstResult(($page - 1) * $pageSize)
            ->setMaxResults($pageSize)
            ->getQuery()
            ->getResult();

        return $this->render('frontoffice/challenge/notifications.html.twig', [
            'notifications' => $list,
            'roleLabel' => $user->getMainRoleLabel(),
            'page' => $page,
            'pagesCount' => $pagesCount,
            'total' => $total,
            'pageSize' => $pageSize,
        ]);
    }

    #[Route('/notifications/{id}/open', name: 'notifications_open', methods: ['GET'])]
    public function open(int $id, Request $request, EntityManagerInterface $em)
    {
        $userId = $request->getSession()->get('user_id');
        if (!$userId) {
            return $this->redirectToRoute('sign');
        }
        $user = $em->getRepository(User::class)->find($userId);
        if (!$user) {
            return $this->redirectToRoute('sign');
        }

        $notification = $em->getRepository(Notification::class)->find($id);
        if (!$notification || $notification->getOwner()?->getId() !== $user->getId()) {
            return $this->redirectToRoute('notifications_index');
        }

        $data = $notification->getData();
        $type = $data['extra']['type'] ?? null;

        $notification->setIsRead(true);
        $em->flush();

        if ($type === 'activity_evaluated') {
            return $this->redirectToRoute('old_activities');
        }
        if ($type === 'activity_submitted') {
            return $this->redirectToRoute('evaluation');
        }

        if ($user->getMainRoleLabel() === 'Supervisor') {
            return $this->redirectToRoute('evaluation');
        }
        return $this->redirectToRoute('old_activities');
    }


    #[Route('/api/notifications/unread', name: 'api_notifications_unread', methods: ['GET'])]
    public function unread(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $userId = $request->getSession()->get('user_id');
        if (!$userId) {
            return new JsonResponse([], 200);
        }
        $user = $em->getRepository(User::class)->find($userId);
        if (!$user) {
            return new JsonResponse([], 200);
        }

        $list = $em->getRepository(Notification::class)->createQueryBuilder('n')
            ->andWhere('n.owner = :owner')
            ->andWhere('n.isRead = :isRead')
            ->setParameter('owner', $user)
            ->setParameter('isRead', false)
            ->orderBy('n.CreatedAt', 'DESC')
            ->getQuery()
            ->getResult();

        $data = array_map(static function (Notification $n) {
            $payload = $n->getData();
            return [
                'id' => $n->getId(),
                'message' => $payload['message'] ?? '',
                'createdAt' => $payload['createdAt'] ?? null,
                'extra' => $payload['extra'] ?? [],
            ];
        }, $list);

        return new JsonResponse($data);
    }

    #[Route('/api/notifications/{id}/read', name: 'api_notifications_mark_read', methods: ['POST'])]
    public function markRead(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $userId = $request->getSession()->get('user_id');
        if (!$userId) {
            return new JsonResponse(['ok' => true]);
        }

        $notification = $em->getRepository(Notification::class)->find($id);
        if (!$notification) {
            return new JsonResponse(['ok' => false], 404);
        }

        $notification->setIsRead(true);
        $em->flush();

        return new JsonResponse(['ok' => true]);
    }
}
