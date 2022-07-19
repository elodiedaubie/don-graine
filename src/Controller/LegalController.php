<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/', name: 'legal')]
class LegalController extends AbstractController
{
    #[Route('cgu', methods: ['GET'], name: '_notice')]
    public function showLegalNotice(): Response
    {
        return $this->render('legal/notice.html.twig');
    }

    #[Route('mentions-legales', methods: ['GET'], name: '_credits')]
    public function showLegalCredit(): Response
    {
        return $this->render('legal/credit.html.twig');
    }
}
