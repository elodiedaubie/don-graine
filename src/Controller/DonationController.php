<?php

namespace App\Controller;

use App\Entity\SeedBatch;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/donation', name: 'donation')]
class DonationController extends AbstractController
{
    #[Route('/add/{id}', name: '_add', requirements: ['id' => '\d+'])]
    public function addDonation(SeedBatch $seedBatch): SeedBatch
    {
        return $seedBatch;
    }
}
