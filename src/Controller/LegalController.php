<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/', name: 'legal')]
class LegalController extends AbstractController
{
    #[Route('cgu', name: '_notice')]
    public function index(): Response
    {
        return $this->render('legal/notice.html.twig');
    }
}
