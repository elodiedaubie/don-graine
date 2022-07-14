<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditUserFormType;
use App\Repository\DonationRepository;
use App\Repository\SeedBatchRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/mon-compte', name: 'user_account')]
#[IsGranted('ROLE_USER')]
class UserAccountController extends AbstractController
{
    #[Route('/', name: '')]
    public function index(
        SeedBatchRepository $seedBatchRepository,
        DonationRepository $donationRepository
    ): Response {

        if ($this->getUSer() && $this->getUSer() instanceof User) {
            $user = $this->getUser();
        }
        $userBatches = $seedBatchRepository->findByOwner($user, ['id' => 'DESC']);
        $availableBatches = [];
        $donations = [];

        if (!empty($userBatches)) {
            foreach ($userBatches as $userBatch) {
                if ($userBatch->isIsAvailable()) {
                    //get available seed batches only
                    $availableBatches[] = $userBatch;
                }
                //get donations made by users
                foreach ($userBatch->getDonations() as $donation) {
                    $donations [] = $donation;
                }
            }
        }

        return $this->render('user_account/index.html.twig', [
            'user' => $user,
            'available_batches' => $availableBatches,
            'requested_donations' => $donationRepository->findByBeneficiary($user, ['createdAt' => 'DESC']),
            'donations' => $donations
        ]);
    }

    #[Route('/modifier', name: '_edit')]
    public function edit(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {

        //check if there is an instance of User
        if ($this->getUser() && $this->getUser() instanceof User) {
            $user = $this->getUser();
        }

        $form = $this->createForm(EditUserFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setUserName($form->get('username')->getData());
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Votre profil a bien été modifié');
            return $this->redirectToRoute('user_account');
        }

        return $this->render('user_account/edit_user.html.twig', [
            "editUserForm" => $form->createView()
        ]);
    }

    #[Route('/mes-demandes', name: '_requests')]
    public function showRequests(): Response
    {

        return $this->render('user_account/show_requests.html.twig');
    }
}
