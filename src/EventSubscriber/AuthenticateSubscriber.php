<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;

class AuthenticateSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    )
    {
    }

    public function onSecurityAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $securityToken = $event->getAuthenticationToken();
        $user = $securityToken->getUser();

        // Verifions si l'objet utilisateur existe
        if ($user instanceof User){
            $user->setConnexion((int) $user->getConnexion() + 1);
            $user->setLastConnectedAt(new \DateTimeImmutable());

            $this->entityManager->flush();
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'security.authentication.success' => 'onSecurityAuthenticationSuccess',
        ];
    }

}
