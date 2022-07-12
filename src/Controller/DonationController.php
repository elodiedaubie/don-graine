<?php

namespace App\Controller;

use App\Entity\SeedBatch;
use App\Entity\User;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\TryCatch;
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

    //private function checkBatchAvaibility
        //test if SeedBatch->isAvailable = true;
        //test if SeedBatch->getDonation = null :
            //null = available
            // notnull = test if status is "annulé") :
                //true = available
                //false = add flash: ce lot n'est plus disponible + setISavailable->false

    #[Route('/add/{id}', name: '_add', requirements: ['id' => '\d+'])]
    public function addDonation(SeedBatch $seedBatch): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $beneficiary = $this->getUser();
        $owner = $seedBatch->getOwner();

        //checkUsersConformity
        if ($this->areUsersDifferents($owner, $beneficiary)) {
            //checkBatchesAvailability
            //create a new donation
            //persist and flush
            $this->addFlash(
                'success',
                'Votre demande de graine a bien été enregistrée, le donateur a été prévenu par email'
            );
        }

        return $this->redirectToRoute('user_account');
    }
}
