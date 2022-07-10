<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\SeedBatch;
use App\Form\AddSeedBatchFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_USER')]
#[Route('/donner', name: 'seed_batch')]
class SeedBatchController extends AbstractController
{
    #[Route('', name: '_add')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
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
            //validate that quantity given is in the right range
            //add X number of the same batch in DB
            $seedBatch->setOwner($user);
            $entityManager->persist($seedBatch);
            $entityManager->flush();
            // add flash if success
            $this->addFlash('success', 'Vos graines sont maintenant disponibles dans notre grainothèque');
            //redirect to user_account
        }

        return $this->renderForm('seed_batch/index.html.twig', [
            'addSeedBatchForm' => $form,
        ]);
    }
}
