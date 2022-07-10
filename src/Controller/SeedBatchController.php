<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/donner', name: 'seed_batch')]
class SeedBatchController extends AbstractController
{
    #[Route('', name: '_add')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        return $this->render('seed_batch/index.html.twig', [
            'controller_name' => 'SeedBatchController',
        ]);
    }
}
