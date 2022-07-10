<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SeedBatchController extends AbstractController
{
    #[Route('/donner', name: 'seed_batch')]
    public function index(): Response
    {
        return $this->render('seed_batch/index.html.twig', [
            'controller_name' => 'SeedBatchController',
        ]);
    }
}
