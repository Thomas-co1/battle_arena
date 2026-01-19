<?php

namespace App\Controller\Admin;

use App\Entity\Player;
use App\Entity\User;
use App\Repository\PlayerRepository;
use App\Repository\UserRepository;
use App\Service\RegistrationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/admin/player')]
class AdminPlayerController extends AbstractController
{
    #[Route('', name: 'admin_player_list', methods: ['GET'])]
    public function list(PlayerRepository $playerRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $players = $playerRepository->findAll();

        return $this->render('admin/player/list.html.twig', [
            'players' => $players,
        ]);
    }

    #[Route('/{id}', name: 'admin_player_show', methods: ['GET'])]
    public function show(Player $player): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/player/show.html.twig', [
            'player' => $player,
        ]);
    }

    #[Route('/new', name: 'admin_player_new', methods: ['GET', 'POST'])]
    public function new(Request $request, RegistrationService $registrationService): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createFormBuilder()
            ->add('email', EmailType::class, ['label' => 'Email'])
            ->add('username', TextType::class, ['label' => 'Nom d\'utilisateur'])
            ->add('plainPassword', PasswordType::class, ['label' => 'Mot de passe'])
            ->add('gamertag', TextType::class, ['label' => 'Pseudo de jeu'])
            ->add('skillLevel', IntegerType::class, ['label' => 'Niveau (1-15)'])
            ->add('mainCharacter', TextType::class, ['label' => 'Personnage'])
            ->add('submit', SubmitType::class, ['label' => 'Créer'])
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
                $registrationService->verifyEmail($user); // Auto-vérifier pour admin
                $this->addFlash('success', 'Joueur créé!');
                return $this->redirectToRoute('admin_player_show', ['id' => $user->getPlayer()->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('admin/player/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_player_edit', methods: ['GET', 'POST'])]
    public function edit(Player $player, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createFormBuilder($player)
            ->add('gamertag', TextType::class, ['label' => 'Pseudo de jeu'])
            ->add('skillLevel', IntegerType::class, ['label' => 'Niveau (1-15)'])
            ->add('mainCharacter', TextType::class, ['label' => 'Personnage'])
            ->add('submit', SubmitType::class, ['label' => 'Mettre à jour'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Profil mis à jour!');
            return $this->redirectToRoute('admin_player_show', ['id' => $player->getId()]);
        }

        return $this->render('admin/player/edit.html.twig', [
            'player' => $player,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_player_delete', methods: ['POST'])]
    public function delete(Player $player, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete' . $player->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($player);
            $entityManager->flush();
            $this->addFlash('success', 'Joueur supprimé!');
        }

        return $this->redirectToRoute('admin_player_list');
    }
}
