<?php

namespace App\Controller;

use App\Entity\MatchResultType;
use App\Repository\MatchResultRepository;
use App\Service\MatchService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/player')]
class PlayerController extends AbstractController
{
    #[Route('/dashboard', name: 'app_player_dashboard', methods: ['GET'])]
    public function dashboard(MatchResultRepository $matchRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $player = $this->getUser()->getPlayer();

        if (!$player) {
            throw $this->createNotFoundException('Profil joueur non trouvé');
        }

        $pendingMatches = $matchRepository->findPlayersPendingMatches($player);
        $allMatches = $player->getAllMatches();

        return $this->render('player/dashboard.html.twig', [
            'player' => $player,
            'pendingMatches' => $pendingMatches,
            'allMatches' => $allMatches,
        ]);
    }

    #[Route('/match/{id}/submit-result', name: 'app_player_submit_result', methods: ['GET', 'POST'])]
    public function submitMatchResult(int $id, Request $request, MatchResultRepository $matchRepository, MatchService $matchService): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $player = $this->getUser()->getPlayer();

        $match = $matchRepository->find($id);
        if (!$match) {
            throw $this->createNotFoundException('Match non trouvé');
        }

        // Vérifier que le joueur participe au match
        if ($match->getPlayer1() !== $player && $match->getPlayer2() !== $player) {
            throw $this->createAccessDeniedException('Vous ne participez pas à ce match');
        }

        // Vérifier si le joueur a déjà soumis un résultat
        $hasSubmitted = ($match->getPlayer1() === $player && $match->getPlayer1Result() !== null) ||
                        ($match->getPlayer2() === $player && $match->getPlayer2Result() !== null);

        if ($hasSubmitted) {
            $this->addFlash('info', 'Vous avez déjà soumis votre résultat pour ce match');
            return $this->redirectToRoute('app_player_dashboard');
        }

        $form = $this->createFormBuilder()
            ->add('result', ChoiceType::class, [
                'choices' => [
                    'Victoire' => MatchResultType::WIN,
                    'Défaite' => MatchResultType::LOSS,
                    'Égalité' => MatchResultType::DRAW,
                ],
                'label' => 'Mon résultat',
                'required' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Soumettre le résultat',
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            try {
                $matchService->submitResult($match, $player, $data['result']);
                $this->addFlash('success', 'Résultat soumis avec succès!');
                return $this->redirectToRoute('app_player_dashboard');
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        $opponent = $match->getPlayer1() === $player ? $match->getPlayer2() : $match->getPlayer1();

        return $this->render('player/submit_result.html.twig', [
            'match' => $match,
            'opponent' => $opponent,
            'form' => $form->createView(),
        ]);
    }
}
