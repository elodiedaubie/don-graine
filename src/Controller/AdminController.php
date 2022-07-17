<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin')]
    public function index(UserRepository $userRepository): Response
    {
        if ($this->getUser() && $this->getUser() instanceof User) {
            $user = $this->getUser();
        }

        return $this->render('admin/index.html.twig', [
            'user' => $user,
            'users' => $userRepository->findAll()
        ]);
    }
}
