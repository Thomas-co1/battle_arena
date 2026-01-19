<?php

namespace App\EventListener;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

class EmailVerificationListener implements EventSubscriberInterface
{
    public function __construct(
        private Security $security,
        private RouterInterface $router,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return;
        }

        // Routes qui ne nécessitent pas de vérification
        $publicRoutes = [
            'app_verify_email',
            'app_login',
            'app_logout',
            'app_register',
            'app_tournament_list',
        ];

        $routeName = $request->attributes->get('_route');
        if (in_array($routeName, $publicRoutes, true)) {
            return;
        }

        // Si l'utilisateur n'a pas confirmé son email, le rediriger
        if (!$user->isVerified()) {
            $event->setResponse(new RedirectResponse(
                $this->router->generate('app_verify_email_pending')
            ));
        }
    }
}
