<?php

namespace App\Service;

use App\Entity\Notif;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class NotifService
{
    private EntityManagerInterface $em;
    private \Psr\Log\LoggerInterface $logger;

    public function __construct(EntityManagerInterface $em, \Psr\Log\LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * Creates and persists a new notification for a user.
     */
    public function notify(User $user, string $message): void
    {
        try {
            $notification = new Notif();
            $notification->setUser($user);
            $notification->setMessage($message);
            $notification->setIsRead(false);
            $notification->setCreatedAt(new \DateTimeImmutable());

            $this->em->persist($notification);
            $this->em->flush();
            $this->logger->info('Notification persisted for user: ' . $user->getId());
        } catch (\Exception $e) {
            $this->logger->error('Failed to persist notification: ' . $e->getMessage());
        }
    }
}
