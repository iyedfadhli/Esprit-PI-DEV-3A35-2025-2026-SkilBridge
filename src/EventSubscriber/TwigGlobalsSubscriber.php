<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class TwigGlobalsSubscriber implements EventSubscriberInterface
{
    private Environment $twig;
    private RequestStack $requestStack;
    private EntityManagerInterface $em;

    public function __construct(Environment $twig, RequestStack $requestStack, EntityManagerInterface $em)
    {
        $this->twig = $twig;
        $this->requestStack = $requestStack;
        $this->em = $em;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onController',
        ];
    }

    public function onController(ControllerEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $user = null;

        if ($request && $request->getSession()->has('user_id')) {
            $userId = $request->getSession()->get('user_id');
            $user = $this->em->getRepository(User::class)->find($userId);
        }

        $this->twig->addGlobal('user', $user);
    }
}
