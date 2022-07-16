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
    public SeedBatchRepository $seedBatchRepository;
    public DonationRepository $donationRepository;

    public function __construct(
        SeedBatchRepository $seedBatchRepository,
        DonationRepository $donationRepository
    ) {
        $this->seedBatchRepository = $seedBatchRepository;
        $this->donationRepository = $donationRepository;
    }

    //get Available Batches for a specific user
    private function getAvailableBatches(User $user): array
    {
        $userBatches = $this->seedBatchRepository->findByOwner($user, ['id' => 'DESC']);
        $availableBatches = [];

        if (!empty($userBatches)) {
            foreach ($userBatches as $userBatch) {
                if ($userBatch->isAvailable()) {
                    //get available seed batches only
                    $availableBatches[] = $userBatch;
                }
            }
        }
        return $availableBatches;
    }

    private function getDonations(User $user): array
    {
        $userBatches = $this->seedBatchRepository->findByOwner($user, ['id' => 'DESC']);
        $donations = [];

        if (!empty($userBatches)) {
            foreach ($userBatches as $userBatch) {
                //get donations made by users
                foreach ($userBatch->getDonations() as $donation) {
                    $donations [] = $donation;
                }
            }
        }
        return $donations;
    }

    #[Route('/', name: '')]
    public function index(): Response
    {
        if ($this->getUSer() && $this->getUSer() instanceof User) {
            $user = $this->getUser();
        }

        return $this->render('user_account/index.html.twig', [
            'user' => $user,
            'available_batches' =>  $this->getAvailableBatches($user),
            'requested_donations' => $this->donationRepository->findByBeneficiary($user, ['createdAt' => 'DESC']),
            'donations' => $this->getDonations($user),
            'favorite_list' =>  $user->getFavoriteList()
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
        //check if there is an instance of User
        if ($this->getUser() && $this->getUser() instanceof User) {
            $user = $this->getUser();
        }

        return $this->render('user_account/show_requests.html.twig', [
            'requested_donations' => $this->donationRepository->findByBeneficiary($user, ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/mes-dons', name: '_donations')]
    public function showDonations(): Response
    {
        //check if there is an instance of User
        if ($this->getUser() && $this->getUser() instanceof User) {
            $user = $this->getUser();
        }

        return $this->render('user_account/show_donations.html.twig', [
            'donations' => $this->getDonations($user)
        ]);
    }

    #[Route('/mon-stock', name: '_available')]
    public function showAvailableBatches(): Response
    {
        //check if there is an instance of User
        if ($this->getUser() && $this->getUser() instanceof User) {
            $user = $this->getUser();
        }

        return $this->render('user_account/show_available_batches.html.twig', [
            'available_batches' =>  $this->getAvailableBatches($user),
        ]);
    }

    #[Route('/mes-favoris', name: '_favorite')]
    public function showFavoriteList(): Response
    {
        //check if there is an instance of User
        if ($this->getUser() && $this->getUser() instanceof User) {
            $user = $this->getUser();
        }

        return $this->render('user_account/show_favorite_list.html.twig', [
            'favorite_list' =>  $user->getFavoriteList(),
        ]);
    }
}
