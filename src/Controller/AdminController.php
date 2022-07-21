<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Donation;
use App\Form\SearchUserFormType;
use App\Repository\UserRepository;
use App\Repository\DonationRepository;
use App\Repository\SeedBatchRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/admin', methods: ['GET', 'POST'], name: 'admin')]
    public function index(
        UserRepository $userRepository,
        DonationRepository $donationRepository,
        SeedBatchRepository $seedBatchRepository,
        Request $request
    ): Response {

        $available = 0;
        if ($this->getUser() && $this->getUser() instanceof User) {
            $user = $this->getUser();
        }

        //search by username form
        $form = $this->createForm(SearchUserFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (!empty($form->get('search')->getData())) {
                //search by username required
                $search = $form->get('search')->getData();
                $users = $userRepository->findLikeUsername($search);
            }
        } else {
            //no search required, display all users
            $users = $userRepository->findAll();
        }

        //get number of available batches to display it
        foreach ($seedBatchRepository->findAll() as $seedBatch) {
            if ($seedBatch->isAvailable()) {
                $available++;
            }
        }

        return $this->renderForm('admin/index.html.twig', [
            'user' => $user,
            'users' => $users,
            'encours' => count($donationRepository->findByStatus(Donation::STATUS[0])),
            'finalise' => count($donationRepository->findByStatus(Donation::STATUS[1])),
            'annule' => count($donationRepository->findByStatus(Donation::STATUS[2])),
            'available' => $available,
            'form' => $form
        ]);
    }
}
