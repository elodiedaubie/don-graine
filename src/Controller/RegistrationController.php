<?php

namespace App\Controller;

use App\Entity\User;
use DateTimeImmutable;
use App\Security\EmailVerifier;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Service\MailerManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;
    private MailerManager $mailerManager;
    private UserRepository $userRepository;

    public function __construct(
        EmailVerifier $emailVerifier,
        MailerManager $mailerManager,
        UserRepository $userRepository
    ) {
        $this->emailVerifier = $emailVerifier;
        $this->mailerManager = $mailerManager;
        $this->userRepository = $userRepository;
    }

    #[Route('/inscription', methods: ['GET', 'POST'], name: 'register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
    ): Response {

        //route not accessible to logged users
        if ($this->getUser() && $this->getUser() instanceof UserInterface) {
            return $this->redirectToRoute('home');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            //get form username to control if it already exists in DB
            $username = $form->getNormData()->getUsername();

            if (!$this->userRepository->findOneBy(['username' => $username])) {
                //there is no user with same username, create the new user
                $user->setCreatedAt(new DateTimeImmutable());
                $entityManager->persist($user);
                $entityManager->flush();

                // generate a signed url and email it to the user and display addflash
                $this->mailerManager->sendVerifyRegistration($user);

                return $this->redirectToRoute('login');
            }
                $form->addError(
                    new FormError(
                        'Il existe déjà un utilisateur avec ce pseudo',
                        null,
                    )
                );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(
        Request $request,
        TranslatorInterface $translator
    ): Response {
        $id = $request->get('id');

        if (null === $id) {
            return $this->redirectToRoute('register');
        }

        $user = $this->userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('register');
        }

        $this->addFlash('success', 'Votre adresse email a bien été vérifiée, veuillez vous connecter');

        return $this->redirectToRoute('login');
    }
}
