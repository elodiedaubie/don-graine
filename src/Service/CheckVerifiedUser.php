<?php

namespace App\Service;

use Exception;
use App\Entity\User;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

/**
 * This class is automaticaly called when login form is submit, because it implement EventSuscriberInterface
 * This class has been made manually because the security bundle did allow connections with is_verified = false
 */

class CheckVerifiedUser implements EventSubscriberInterface
{
    //check if email is verified after password and email verification
    public function onCheckPassport(CheckPassportEvent $event)
    {
        $user = $event->getPassport()->getUser();
        if (!$user instanceof User) {
            throw new Exception('Unexpected user type');
        }

        if (!$user->isVerified()) {
            throw new CustomUserMessageAuthenticationException(
                'Veuillez valider votre compte par email avant de vous connecter.'
            );
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            CheckPassportEvent::class => ['onCheckPassport', -10],
        ];
    }
}
