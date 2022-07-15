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
    public MailerManager $mailerManager;

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

    private function isBatchAvailable(SeedBatch $seedBatch): bool
    {
        if ($seedBatch instanceof SeedBatch) {
            if (!$seedBatch->isIsAvailable()) {
                //isAvailable is set to false
                return false;
            }
            foreach ($seedBatch->getDonations() as $donation) {
                if (
                    $donation->getStatus() === Donation::STATUS[0]
                    || $donation->getStatus() === Donation::STATUS[1]
                ) {
                    //there is already an active donation for this batch
                    return false;
                }
            }
            return true;
        }
    }

    #[Route('/add/{id}', name: '_add', requirements: ['id' => '\d+'])]
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
        if (!$this->isBatchAvailable($seedBatch)) {
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
        //batch is not available anymore
        $seedBatch->setIsAvailable(0);
        $entityManager->persist($seedBatch);
        $entityManager->flush($seedBatch);

        $this->mailerManager->sendDonationAlert($owner, $beneficiary, $seedBatch);

        $this->addFlash(
            'success',
            'Votre demande de graine a bien été enregistrée, le donateur a été prévenu par email'
        );

        return $this->redirectToRoute('user_account');
    }
}
