<?php

namespace App\Controller;

use App\Entity\User;
use DateTimeImmutable;
use App\Entity\Donation;
use App\Entity\SeedBatch;
use App\Service\MailerManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_USER')]
#[Route('/don', name: 'donation')]
class DonationController extends AbstractController
{
    private MailerManager $mailerManager;
    private EntityManagerInterface $entityManager;

    public function __construct(
        MailerManager $mailerManager,
        EntityManagerInterface $entityManager
    ) {
        $this->mailerManager = $mailerManager;
        $this->entityManager = $entityManager;
    }

    /**
     * Handle a request of donation made by user on seedbank
     */
    #[Route('/{id}/ajouter', methods: ['GET'], name: '_add', requirements: ['id' => '\d+'])]
    public function addDonation(SeedBatch $seedBatch): Response
    {
        if ($this->getUser() && $this->getUser() instanceof User) {
            if ($seedBatch->getOwner() === $this->getUser()) {
                //Owner and Beneficiary are same user
                $this->addFlash(
                    'danger',
                    'Vous ne pouvez pas réserver votre propre lot'
                );
                return $this->redirectToRoute('home');
            }
            if (!$seedBatch->isAvailable()) {
                //there is a donation in progress or done for this batch,
                //it can't be modified afterwards
                $this->addFlash(
                    'danger',
                    'Désolé.e, ce lot a déjà été réservé par quelqu\'un d\'autre'
                );
                return $this->redirectToRoute('home');
            }
            //create new donation
            $donation = new Donation();
            $donation->setBeneficiary($this->getUser());
            $donation->setStatus(Donation::STATUS[0]);
            $donation->setCreatedAt(new DateTimeImmutable());
            $donation->setSeedBatch($seedBatch);
            $this->entityManager->persist($donation);
            $this->entityManager->persist($seedBatch);
            $this->entityManager->flush();
            //send email to owner, using a service
            $this->mailerManager->sendDonationAlert($seedBatch->getOwner(), $this->getUser(), $seedBatch);
            //display adflash to confirm action to beneficiary
            $this->addFlash(
                'success',
                'Votre demande de graine a bien été enregistrée, le donateur a été prévenu par email'
            );
        }
        return $this->redirectToRoute('user_account');
    }

    /**
     * Change donation status to cancel if original status was in progress
     * Check if User is allowed to do it
     */
    #[Route('/{id}/annuler', methods: ['GET'], name: '_cancel', requirements: ['id' => '\d+'])]
    public function cancelDonation(Donation $donation): Response
    {
        if ($this->getUser() && $this->getUser() instanceof User) {
            if ($donation->getStatus() !== Donation::STATUS[0]) {
                //status is not in progress, status can't be change
                $this->addFlash('danger', 'Seuls les dons en cours peuvent changer de statut');
                return $this->redirectToRoute('user_account');
            }

            if (
                $donation->getBeneficiary() !== $this->getUser()
                && $donation->getSeedBatch()->getOwner() !== $this->getUser()
            ) {
                //connected user is neither beneficiary or owner, not allowed to change anything
                $this->addFlash('danger', 'Seuls le bénéficiaire ou le donateur sont autorisés à changer de statut');
                return $this->redirectToRoute('user_account');
            }

            $donation->setStatus(Donation::STATUS[2]);
            $this->entityManager->flush($donation);
            $this->addFlash(
                'success',
                'Le statut du don a bien été mis à jour, le lot est de nouveau disponible dans la grainothèque'
            );

            //send an email to the other user
            if ($this->getUser() === $donation->getBeneficiary()) {
                //user is beneficiary, alert owner by mail
                $addressee = $donation->getSeedBatch()->getOwner();
            }
            if ($this->getUser() === $donation->getSeedBatch()->getOwner()) {
                //user is owner, alert beneficiary by mail
                $addressee = $donation->getBeneficiary();
            }
            $this->mailerManager->sendDonationCanceled(
                $addressee,
                $donation
            );
        }
        return $this->redirectToRoute('user_account');
    }

    /**
     * Change donation status to finalized if original status was in progress
     * Check if user is allowed to do it
     */
    #[Route('/{id}/finaliser', methods: ['GET'], name: '_finalise', requirements: ['id' => '\d+'])]
    public function finaliseDonation(Donation $donation): Response
    {
        if ($this->getUser() && $this->getUser() instanceof User) {
            if ($donation->getStatus() !== Donation::STATUS[0]) {
                //status is not in progress
                $this->addFlash('danger', 'Seuls les dons en cours peuvent changer de statut');
                return $this->redirectToRoute('user_account');
            }

            if ($donation->getBeneficiary() !== $this->getUser()) {
                //connected user is not beneficiary
                $this->addFlash('danger', 'Seuls le bénéficiaire du don est autorisé à changer de statut');
                return $this->redirectToRoute('user_account');
            }

            $donation->setStatus(Donation::STATUS[1]);
            $this->entityManager->flush($donation);
            $this->addFlash('success', 'Le statut de votre don a bien été mis à jour');
            $this->mailerManager->sendDonationCompleted(
                $donation->getSeedBatch()->getOwner(),
                $donation
            );
        }

        return $this->redirectToRoute('user_account');
    }
}
