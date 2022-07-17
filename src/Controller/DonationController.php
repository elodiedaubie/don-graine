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
#[Route('/donation', name: 'donation')]
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

    //test if beneficary and owners are not the same
    private function areUsersDifferents(User $owner, User $beneficiary): bool
    {
        if ($owner instanceof User && $beneficiary instanceof User) {
            if ($owner !== $beneficiary) {
                return true;
            }
            return false;
        }
    }

    #[Route('/{id}/ajouter', name: '_add', requirements: ['id' => '\d+'])]
    public function addDonation(SeedBatch $seedBatch): Response
    {
        if ($this->getUser() && $this->getUser() instanceof User) {
            $beneficiary = $this->getUser();
            $owner = $seedBatch->getOwner();

            if (!$this->areUsersDifferents($owner, $beneficiary)) {
                //Owner and Beneficiary are different people
                $this->addFlash(
                    'danger',
                    'Vous ne pouvez pas réserver votre propre lot'
                );
                return $this->redirectToRoute('home');
            }
            if (!$seedBatch->isAvailable()) {
                //there is a donation going on or over for this batch
                $this->addFlash(
                    'danger',
                    'Désolé.e, ce lot a déjà été réservé par quelqu\'un d\'autre'
                );
                return $this->redirectToRoute('home');
            }
            //create new donation
            $donation = new Donation();
            $donation->setBeneficiary($beneficiary);
            $donation->setStatus(Donation::STATUS[0]);
            $donation->setCreatedAt(new DateTimeImmutable());
            $donation->setSeedBatch($seedBatch);
            $this->entityManager->persist($donation);
            $this->entityManager->persist($donation);
            $this->entityManager->flush($donation);
            $this->entityManager->persist($seedBatch);
            $this->entityManager->flush($seedBatch);
            //send email to owner
            $this->mailerManager->sendDonationAlert($owner, $beneficiary, $seedBatch);
            //display adflash to confirm action to beneficiary
            $this->addFlash(
                'success',
                'Votre demande de graine a bien été enregistrée, le donateur a été prévenu par email'
            );
        }
        return $this->redirectToRoute('user_account');
    }

    #[Route('/{id}/annuler', name: '_cancel', requirements: ['id' => '\d+'])]
    public function cancelDonation(Donation $donation): Response
    {
        if ($this->getUser() && $this->getUser() instanceof User) {
            $user = $this->getUser();

            //control is status en cours - other donations can't be canceled
            if ($donation->getStatus() !== Donation::STATUS[0]) {
                //status is not "en cours"
                $this->addFlash('danger', 'Seuls les dons en cours peuvent changer de statut');
                return $this->redirectToRoute('user_account');
            }

            //control if user allowed to canceled
            if (
                $donation->getBeneficiary() !== $user
                && $donation->getSeedBatch()->getOwner() !== $user
            ) {
                //connected user is neither beneficiary or owner
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
            if ($user === $donation->getBeneficiary()) {
                //user is beneficiary, alert owner by mail
                $addressee = $donation->getSeedBatch()->getOwner();
            }
            if ($user === $donation->getSeedBatch()->getOwner()) {
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

    #[Route('/{id}/finaliser', name: '_finalise', requirements: ['id' => '\d+'])]
    public function finaliseDonation(Donation $donation): Response
    {
        if ($this->getUser() && $this->getUser() instanceof User) {
            $user = $this->getUser();

            if ($donation->getStatus() !== Donation::STATUS[0]) {
                //status is not "en cours"
                $this->addFlash('danger', 'Seuls les dons en cours peuvent changer de statut');
                return $this->redirectToRoute('user_account');
            }

            if ($donation->getBeneficiary() !== $user()) {
                //connected user is not beneficiary
                $this->addFlash('danger', 'Seuls le bénéficiaire du don est autorisé à changer de statut');
                return $this->redirectToRoute('user_account');
            }

            $donation->setStatus(Donation::STATUS[1]);
            $$this->entityManager->flush($donation);
            $this->addFlash('success', 'Le statut de votre don a bien été mis à jour');
            $this->mailerManager->sendDonationCompleted(
                $donation->getSeedBatch()->getOwner(),
                $donation
            );
        }

        return $this->redirectToRoute('user_account');
    }
}
