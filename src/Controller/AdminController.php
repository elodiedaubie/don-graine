<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        if ($this->getUser() && $this->getUser() instanceof User) {
            $user = $this->getUser();
        }
        return $this->render('admin/index.html.twig', [
            'user' => $user
        ]);
    }
}
