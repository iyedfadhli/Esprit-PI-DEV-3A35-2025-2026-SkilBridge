<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class TwigGlobalsSubscriber implements EventSubscriberInterface
{
    private Environment $twig;
    private EntityManagerInterface $em;
    private \Psr\Log\LoggerInterface $logger;

    public function __construct(Environment $twig, EntityManagerInterface $em, \Psr\Log\LoggerInterface $logger)
    {
        $this->twig = $twig;
        $this->em = $em;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onController',
        ];
    }

    public function onController(ControllerEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $user = null;

        if ($request->hasSession() && $request->getSession()->has('user_id')) {
            $userId = $request->getSession()->get('user_id');
            $user = $this->em->getRepository(User::class)->find($userId);
            $this->logger->info('TwigGlobalsSubscriber: User found from session. ID: ' . ($user ? $user->getId() : 'NOT_FOUND_IN_DB'));
        }

        $this->twig->addGlobal('user', $user);

        // Add unread notifications data
        $unreadCount = 0;
        $unreadNotifications = [];
        if ($user) {
            $repo = $this->em->getRepository(\App\Entity\Notif::class);
            $unreadCount = $repo->countUnreadByUser($user);
            $unreadNotifications = $repo->findUnreadByUser($user);
            $this->logger->info('TwigGlobalsSubscriber: Unread count for user ' . $user->getId() . ' is ' . $unreadCount);
        }
        $this->twig->addGlobal('unread_notifications_count', $unreadCount);
        $this->twig->addGlobal('unread_notifications', $unreadNotifications);
    }
}