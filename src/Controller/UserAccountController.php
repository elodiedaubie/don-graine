<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class UserAccountController extends AbstractController
{
    #[Route('/mon-compte', name: 'user_account')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($this->getUSer() && $this->getUSer() instanceof User) {
            $user = $this->getUser();
        }

        return $this->render('user_account/index.html.twig', [
            'user' => $user,
        ]);
    }
}
