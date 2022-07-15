<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\SeedBatch;
use App\Form\AddSeedBatchFormType;
use App\Repository\SeedBatchRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_USER')]
#[Route('/', name: 'seed_batch')]
class SeedBatchController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('donner', name: '_add')]
    public function index(Request $request): Response
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

        return $this->renderForm('seed_batch/index.html.twig', [
            'addSeedBatchForm' => $form,
        ]);
    }

    //Manager Registry is an argument because required by parent construct of repository
    #[Route('grainotheque', name: '_show')]
    public function showSeedBatches(
        SeedBatchRepository $seedBatchRepository,
        ManagerRegistry $managerRegistry
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $seedBatches = $seedBatchRepository->findBy(
            ['isAvailable' => true],
            ['id' => 'DESC']
        );

        return $this->render('seed_batch/show.html.twig', [
            'seed_batches' => $seedBatches
        ]);
    }

    #[Route('graine/{id}/favorite/', name: '_favorite', requirements: ['id' => '\d+'], methods: ["GET"])]
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
        return $this->redirectToRoute('home');
    }
}
