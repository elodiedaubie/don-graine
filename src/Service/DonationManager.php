<?php

namespace App\Service;

use App\Entity\Donation;
use App\Entity\SeedBatch;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DonationManager extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    //if there is a least one going donation in progress return true
    //if there is only canceled or terminated donations, return false
    public function hasDonationInProgress(Collection $donations): bool
    {
        foreach ($donations as $donation) {
            if ($donation->getStatus() === Donation::STATUS[0]) {
                //there is already an active donation for this batch
                return true;
            }
        }
        return false;
    }

    //entityManager->flush has to be made after using this function, to avoid unnecessary delations
    public function deleteDonations(SeedBatch $seedBatch): void
    {
        if (!empty($seedBatch->getDonations())) {
            foreach ($seedBatch->getDonations() as $donation) {
                $this->entityManager->remove($donation);
            }
        }
    }
}
