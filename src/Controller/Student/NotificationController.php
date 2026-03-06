<?php
namespace App\Controller\Student;

use App\Entity\Notif;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/student')]
class NotificationController extends AbstractController
{
    #[Route('/notifications/mark-read', name: 'student_notifications_mark_read', methods: ['GET'])]
    public function markAllRead(Request $request, EntityManagerInterface $em): Response
    {
        $userId = $request->getSession()->get('user_id');
        if (!$userId) {
            return $this->redirectToRoute('sign');
        }
        $user = $em->getRepository(User::class)->find($userId);
        if (!$user) {
            return $this->redirectToRoute('sign');
        }

        $notifications = $em->getRepository(Notif::class)->findBy([
            'user' => $user,
            'isRead' => false
        ]);

        foreach ($notifications as $n) {
            $n->setIsRead(true);
        }

        $em->flush();

        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?: $this->generateUrl('home'));
    }
}

