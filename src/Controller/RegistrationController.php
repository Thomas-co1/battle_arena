<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\RegistrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(Request $request, RegistrationService $registrationService): Response
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

                // TODO: Envoyer email de confirmation
                // TODO: Rediriger vers page d'attente de confirmation

                $this->addFlash('success', 'Inscription réussie! Veuillez vérifier votre email.');
                return $this->redirectToRoute('app_tournament_list');
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('registration/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
