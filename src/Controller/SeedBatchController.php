<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\SeedBatch;
use App\Form\AddSeedBatchFormType;
use App\Form\EditSeedBatchFormType;
use App\Form\SearchBatchFormType;
use App\Repository\SeedBatchRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_USER')]
#[Route('/grainotheque', name: 'seed_batch')]
class SeedBatchController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    //check if user is the batch's owner - if not display addflash
    private function isUserOwner(
        User $user,
        SeedBatch $seedBatch
    ): bool {
        if (
            $user instanceof User
            && $seedBatch->getOwner()
            && $seedBatch->getOwner() instanceof User
        ) {
            if ($user === $seedBatch->getOwner()) {
                return true;
            }
        }
        $this->addFlash('danger', 'Ce lot ne peut être modifié ou supprimé que par son.sa propriétaire');
        return false;
    }

    /*
    * Show available seed batches in seedbank
    */
    #[Route('/', methods: ['GET', 'POST'], name: '_show')]
    public function index(
        SeedBatchRepository $seedBatchRepository,
        Request $request
    ): Response {

        $availableSeedBatches = [];
        $seedBatches = $seedBatchRepository->findAll();

        //search by plant name form
        $form = $this->createForm(SearchBatchFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (!empty($form->get('search')->getData())) {
                //search by plant name required
                $search = $form->get('search')->getData();
                $seedBatches = $seedBatchRepository->findLikeName($search);
            }
        }
        //no search required, show all available batches
        if (!empty($seedBatches)) {
            foreach ($seedBatches as $seedBatch) {
                if ($seedBatch->isAvailable()) {
                    $availableSeedBatches[] = $seedBatch;
                }
            }
        }

        return $this->renderForm('seed_batch/index.html.twig', [
            'seedBatches' => $availableSeedBatches,
            'form' => $form
        ]);
    }

    /**
     * Handle add seed batch form
     */
    #[Route('/donner', methods: ['GET', 'POST'], name: '_add')]
    public function addSeedBatch(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        //get connected user to set batch owner with it
        if ($this->getUser() && $this->getUser() instanceof User) {
            $user = $this->getUser();
        }
        //create form
        $seedBatch = new SeedBatch();
        $form = $this->createForm(AddSeedBatchFormType::class, $seedBatch);

        //handle form and request
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //get batch quantity given by user ("X"), unmapped by Doctrine, validate it and create x number of batches
            $batchQuantity = $form->get('batchQuantity')->getData();
            //validate that quantity given is in the right range
            if (is_int($batchQuantity)) {
                if ($batchQuantity <= SeedBatch::MAXBATCHADDED || $batchQuantity >= SeedBatch::MINBATCHADDED) {
                    //add X number of the same batch in DB
                    for ($i = 0; $i < $batchQuantity; $i++) {
                        //new instance of Batch mandatory to serialize batch creation
                        $seedBatch = new SeedBatch();
                        $seedBatch->setPlant($form->get('plant')->getData());
                        $seedBatch->setSeedQuantity($form->get('seedQuantity')->getData());
                        $seedBatch->setQuality($form->get('quality')->getData());
                        $seedBatch->setOwner($user);
                        $this->entityManager->persist($seedBatch);
                    }
                    $this->entityManager->flush();
                    // add flash if success
                    $this->addFlash('success', 'Vos graines sont maintenant disponibles dans notre grainothèque');
                    //redirect to user_account
                    return $this->redirectToRoute('user_account');
                }
            }
        }

        return $this->renderForm('seed_batch/add.html.twig', [
            'addSeedBatchForm' => $form,
        ]);
    }

    /**
     * edit a seed batch for owners
     */
    #[Route('/{id}/modifier', methods: ['GET', 'POST'], name: '_edit', requirements: ['id' => '\d+'])]
    public function editSeedBatch(
        Request $request,
        SeedBatch $seedBatch
    ): Response {

        if (!$this->isUserOwner($this->getUser(), $seedBatch)) {
            return $this->redirectToRoute('home');
        }

        //create form
        $form = $this->createForm(EditSeedBatchFormType::class, $seedBatch);
        $form->handleRequest($request);

        //check if there is already donations for this batch
        if (!empty($seedBatch->getDonations())) {
            if (!$seedBatch->isAvailable()) {
                //batch has a donation going on, it cannot be modify
                    $this->addFlash(
                        'danger',
                        'Ce lot a déjà été demandé par quelqu\'un, vous ne pouvez plus le modifier'
                    );
                    return $this->redirectToRoute('home');
            }
            //there is only donation(s) with canceled status, remove it/them
            foreach ($seedBatch->getDonations() as $donation) {
                $this->entityManager->remove($donation);
            }
        }

        //handle request
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($seedBatch);
            $this->entityManager->flush();
            $this->addFlash('success', 'Votre lot de graines a bien été modifié');
            return $this->redirectToRoute('user_account');
        }

        return $this->renderForm('seed_batch/edit.html.twig', [
            'seedBatch' => $seedBatch,
            'editSeedBatchForm' => $form,
        ]);
    }

    /**
     * delete a seed batch for owner
     */
    #[Route('/{id}/supprimer', methods: ['GET'], name: '_delete', requirements: ['id' => '\d+'])]
    public function deleteSeedBatch(
        SeedBatch $seedBatch
    ): Response {

        if (!$this->isUserOwner($this->getUser(), $seedBatch)) {
            return $this->redirectToRoute('home');
        }
        //user is seedBatch's owner
        if (!empty($seedBatch->getDonations())) {
            if (!$seedBatch->isAvailable()) {
                //batch has a donation going on, it cannot be deleted
                    $this->addFlash(
                        'danger',
                        'Ce lot a déjà été demandé par quelqu\'un, vous ne pouvez plus le supprimer'
                    );
                    return $this->redirectToRoute('home');
            }
            //there is only donation(s) with canceled status, remove it/them
            foreach ($seedBatch->getDonations() as $donation) {
                $this->entityManager->remove($donation);
            }
        }
        //the is no donations for this batch, remove it
        $this->entityManager->remove($seedBatch);
        $this->entityManager->flush();
        $this->addFlash('success', 'votre lot a bien été supprimé');
        return $this->redirectToRoute('user_account');
    }

    #[Route('/{id}/favorite/', methods: ["GET"], name: '_favorite', requirements: ['id' => '\d+'])]
    public function handleFavoriteList(SeedBatch $seedBatch): Response
    {
        if ($this->getUser() !== null) {
            if ($this->getUser()->hasInFavorites($seedBatch)) {
                $this->getUser()->removeFavoriteList($seedBatch);
            } else {
                $this->getUser()->addFavoriteList($seedBatch);
            }
            $this->entityManager->flush();
        }
        return $this->redirectToRoute('seed_batch_show');
    }
}
