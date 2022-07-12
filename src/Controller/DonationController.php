<?php

namespace App\Controller;

use App\Entity\Donation;
use App\Entity\SeedBatch;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/donation', name: 'donation')]
class DonationController extends AbstractController
{
    //test if beneficary and owners are not the same
    private function areUsersDifferents(User $owner, User $beneficiary): bool
    {
        if ($owner instanceof User && $beneficiary instanceof User) {
            if ($owner != $beneficiary) {
                return true;
            }
            return false;
        }
    }

    //test is SeedBatch propertiy is set to true and if there is not another donation going on
    private function isBatchAvailable(SeedBatch $seedBatch): bool
    {
        if ($seedBatch instanceof SeedBatch) {
            if ($seedBatch->isIsAvailable()) {
                //isAvailable is set to true
                foreach ($seedBatch->getDonations() as $donation) {
                    if (
                        $donation->getStatus() !== Donation::STATUS[0]
                        && $donation->getStatus() !== Donation::STATUS[1]
                    ) {
                        //the is no donations for this batch with a donation over or going on
                        return true;
                    }
                }
            }
        }
        return false;
    }

    #[Route('/add/{id}', name: '_add', requirements: ['id' => '\d+'])]
    public function addDonation(SeedBatch $seedBatch): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $beneficiary = $this->getUser();
        $owner = $seedBatch->getOwner();

        if ($this->areUsersDifferents($owner, $beneficiary)) {
            //Owner and Beneficiary are different people
            if ($this->isBatchAvailable($seedBatch)) {
                //isAvailable is set to true for this batch
                // and there is no donation going on or over (except of canceled ones)
                $this->addFlash(
                    'success',
                    'Votre demande de graine a bien été enregistrée, le donateur a été prévenu par email'
                );
            }
        }

        return $this->redirectToRoute('user_account');
    }
}
