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

    public function __construct(MailerManager $mailerManager)
    {
        $this->mailerManager = $mailerManager;
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
    public function addDonation(
        SeedBatch $seedBatch,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

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
        $entityManager->persist($donation);
        $entityManager->persist($donation);
        $entityManager->flush($donation);
        $entityManager->persist($seedBatch);
        $entityManager->flush($seedBatch);

        $this->mailerManager->sendDonationAlert($owner, $beneficiary, $seedBatch);

        $this->addFlash(
            'success',
            'Votre demande de graine a bien été enregistrée, le donateur a été prévenu par email'
        );

        return $this->redirectToRoute('user_account');
    }

    #[Route('/{id}/annuler', name: '_cancel', requirements: ['id' => '\d+'])]
    public function cancelDonation(
        Donation $donation,
        EntityManagerInterface $entityManager
    ): Response {

        if ($donation->getStatus() !== Donation::STATUS[0]) {
            //status is not "en cours"
            $this->addFlash('danger', 'Seuls les dons en cours peuvent changer de statut');
            return $this->redirectToRoute('user_account');
        }

        if ($donation->getBeneficiary() !== $this->getUser()) {
            //connected user is not beneficiary
            $this->addFlash('danger', 'Seuls le bénéficiaire du don est autorisé à changer de statut');
            return $this->redirectToRoute('user_account');
        }

        $donation->setStatus(Donation::STATUS[1]);
        $entityManager->flush($donation);
        $this->addFlash('success', 'Le statut de votre don a bien été mis à jour');
        $this->mailerManager->sendDonationCompleted(
            $donation->getSeedBatch()->getOwner(),
            $donation
        );
        return $this->redirectToRoute('user_account');
    }
}
