<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class NotificationService
{
    private EntityManagerInterface $em;
    private HubInterface $hub;

    public function __construct(EntityManagerInterface $em, HubInterface $hub)
    {
        $this->em = $em;
        $this->hub = $hub;
    }

    /**
     * @param array<string, mixed> $extra
     */
    public function notify(User $user, string $message, array $extra = []): void
    {
        $notification = new Notification();
        $notification->setOwner($user);
        $notification->setIsRead(false);
        $notification->setCreatedAt(new \DateTimeImmutable());
        $notification->setData([
            'message' => $message,
            'createdAt' => $notification->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'extra' => $extra,
        ]);

        $this->em->persist($notification);
        $this->em->flush();

        $topic = sprintf('/notifications/%d', $user->getId());
        $payload = json_encode([
            'id' => $notification->getId(),
            'message' => $message,
            'createdAt' => $notification->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'extra' => $extra,
        ], JSON_UNESCAPED_UNICODE);
        if ($payload === false) {
            $payload = '{}';
        }

        $update = new Update($topic, $payload);
        $this->hub->publish($update);
    }
}

