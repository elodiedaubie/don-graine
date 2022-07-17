<?php

namespace App\Controller;

use App\Entity\Donation;
use App\Entity\User;
use App\Repository\DonationRepository;
use App\Repository\SeedBatchRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin')]
    public function index(
        UserRepository $userRepository,
        DonationRepository $donationRepository,
        SeedBatchRepository $seedBatchRepository
    ): Response {

        $available = 0;

        if ($this->getUser() && $this->getUser() instanceof User) {
            $user = $this->getUser();
        }

        foreach ($seedBatchRepository->findAll() as $seedBatch) {
            if ($seedBatch->isAvailable()) {
                $available++;
            }
        }

        return $this->render('admin/index.html.twig', [
            'user' => $user,
            'users' => $userRepository->findAll(),
            'encours' => count($donationRepository->findByStatus(Donation::STATUS[0])),
            'finalise' => count($donationRepository->findByStatus(Donation::STATUS[1])),
            'annule' => count($donationRepository->findByStatus(Donation::STATUS[2])),
            'available' => $available
        ]);
    }
}
