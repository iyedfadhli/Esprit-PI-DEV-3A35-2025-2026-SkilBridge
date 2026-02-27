<?php

namespace App\EventSubscriber;

use App\Event\ApplicationStatusChangedEvent;
use App\Service\NotifService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NotificationSubscriber implements EventSubscriberInterface
{
    private NotifService $notifService;
    private \Psr\Log\LoggerInterface $logger;

    public function __construct(NotifService $notifService, \Psr\Log\LoggerInterface $logger)
    {
        $this->notifService = $notifService;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ApplicationStatusChangedEvent::class => 'onApplicationStatusChanged',
        ];
    }

    public function onApplicationStatusChanged(ApplicationStatusChangedEvent $event): void
    {
        $this->logger->info('ApplicationStatusChangedEvent received');
        $application = $event->getApplication();
        $student = $application->getCv()?->getUser();

        if ($student) {
            $this->logger->info('Student found for notification: ' . $student->getEmail());
            $status = $application->getStatus();
            $offerTitle = $application->getOffer()?->getTitle() ?? 'an offer';

            $message = sprintf(
                "Your application for \"%s\" is now %s.",
                $offerTitle,
                $status
            );

            $this->notifService->notify($student, $message);
        }
    }
}
