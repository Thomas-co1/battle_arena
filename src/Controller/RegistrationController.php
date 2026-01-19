<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\EmailVerificationService;
use App\Service\RegistrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(Request $request, RegistrationService $registrationService, EmailVerificationService $emailService): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_tournament_list');
        }

        $form = $this->createFormBuilder()
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => true,
            ])
            ->add('username', TextType::class, [
                'label' => 'Nom d\'utilisateur',
                'required' => true,
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'required' => true,
            ])
            ->add('gamertag', TextType::class, [
                'label' => 'Pseudo de jeu',
                'required' => true,
            ])
            ->add('skillLevel', IntegerType::class, [
                'label' => 'Niveau (1-15)',
                'required' => true,
            ])
            ->add('mainCharacter', TextType::class, [
                'label' => 'Personnage principal',
                'required' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'S\'inscrire',
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $user = $registrationService->register(
                    $data['email'],
                    $data['username'],
                    $data['plainPassword'],
                    $data['gamertag'],
                    $data['skillLevel'],
                    $data['mainCharacter']
                );

                // Envoyer l'email de confirmation
                $emailService->sendVerificationEmail($user);

                $this->addFlash('success', 'Inscription réussie! Veuillez vérifier votre email pour activer votre compte.');
                return $this->redirectToRoute('app_verify_email_pending');
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('registration/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/verify-email/pending', name: 'app_verify_email_pending')]
    public function verifyEmailPending(): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        if ($this->getUser()->isVerified()) {
            return $this->redirectToRoute('app_tournament_list');
        }

        return $this->render('registration/verify_email.html.twig', [
            'email' => $this->getUser()->getEmail(),
        ]);
    }
}

