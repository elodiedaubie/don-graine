<?php

namespace App\Service;

use App\Entity\Donation;
use App\Entity\User;
use App\Entity\SeedBatch;
use App\Security\EmailVerifier;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MailerManager extends AbstractController
{
    private MailerInterface $mailerInterface;
    private EmailVerifier $emailVerifier;

    public function __construct(
        MailerInterface $mailerInterface,
        EmailVerifier $emailVerifier
    ) {
        $this->mailerInterface = $mailerInterface;
        $this->emailVerifier = $emailVerifier;
    }

    /**
     *  mail sent after the registration, with link to confirm e-mail adress
     */
    public function sendVerifyRegistration(User $user): void
    {
        // generate a signed url and email it to the user
        $this->emailVerifier->sendEmailConfirmation(
            'app_verify_email',
            $user,
            (new TemplatedEmail())
                ->from(new Address('noreply@grainesenlair.com', 'Graines en l\'air'))
                ->to($user->getEmail())
                ->subject('Veuillez confirmer votre email')
                ->htmlTemplate('email/confirmation_email.html.twig')
        );

        $this->addFlash(
            'warning',
            'Un email va vous être envoyé afin de finaliser votre inscription.'
        );
    }

    /**
    * mail sent to reset password
    */
    public function sendResetPassword(User $user, ResetPasswordToken $resetToken): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('noreply@grainesenlair.com', 'Graines en l\'air'))
            ->to($user->getEmail())
            ->subject('Votre demande de réinitialisation de mot de passe')
            ->htmlTemplate('email/reset_password.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ])
        ;
        $this->mailerInterface->send($email);
    }

    public function sendDonationAlert(User $owner, User $beneficiary, SeedBatch $seedBatch): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('noreply@grainesenlair.com', 'Graines en l\'air'))
            ->to($owner->getEmail())
            ->subject('Nouvelle demande de lot de graines')
            ->htmlTemplate('email/donation_alert.html.twig')
            ->context([
                'owner' => $owner,
                'beneficiary' => $beneficiary,
                'seedBatch' => $seedBatch
            ])
        ;
        $this->mailerInterface->send($email);
    }

    public function sendDonationCompleted(User $owner, Donation $donation): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('noreply@grainesenlair.com', 'Graines en l\'air'))
            ->to($owner->getEmail())
            ->subject('Votre don est terminé')
            ->htmlTemplate('email/donation_completed.html.twig')
            ->context([
                'owner' => $owner,
                'donation' => $donation
            ])
        ;
        $this->mailerInterface->send($email);
    }

    public function sendDonationCanceled(User $addressee, Donation $donation): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('noreply@grainesenlair.com', 'Graines en l\'air'))
            ->to($addressee->getEmail())
            ->subject('Votre don est annulé')
            ->htmlTemplate('email/donation_canceled.html.twig')
            ->context(['donation' => $donation])
        ;
        $this->mailerInterface->send($email);
    }
}
