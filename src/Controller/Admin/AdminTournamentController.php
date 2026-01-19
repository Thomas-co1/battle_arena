<?php

namespace App\Controller\Admin;

use App\Entity\Player;
use App\Entity\Tournament;
use App\Repository\PlayerRepository;
use App\Repository\TournamentRepository;
use App\Service\MatchService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/tournament')]
class AdminTournamentController extends AbstractController
{
    #[Route('', name: 'admin_tournament_list', methods: ['GET'])]
    public function list(TournamentRepository $tournamentRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $tournaments = $tournamentRepository->findAll();

        return $this->render('admin/tournament/list.html.twig', [
            'tournaments' => $tournaments,
        ]);
    }

    #[Route('/{id}', name: 'admin_tournament_show', methods: ['GET'])]
    public function show(Tournament $tournament): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/tournament/show.html.twig', [
            'tournament' => $tournament,
        ]);
    }

    #[Route('/{id}/matches', name: 'admin_tournament_matches', methods: ['GET'])]
    public function matches(Tournament $tournament): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $matches = $tournament->getMatches();

        return $this->render('admin/tournament/matches.html.twig', [
            'tournament' => $tournament,
            'matches' => $matches,
        ]);
    }

    #[Route('/{tournamentId}/match/create', name: 'admin_match_create', methods: ['GET', 'POST'])]
    public function createMatch(int $tournamentId, Request $request, TournamentRepository $tournamentRepository, PlayerRepository $playerRepository, MatchService $matchService): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $tournament = $tournamentRepository->find($tournamentId);
        if (!$tournament) {
            throw $this->createNotFoundException('Tournoi non trouvé');
        }

        $players = $playerRepository->findAll();

        $form = $this->createFormBuilder()
            ->add('player1', TextType::class, ['label' => 'Joueur 1 (ID ou gamertag)', 'required' => true])
            ->add('player2', TextType::class, ['label' => 'Joueur 2 (ID ou gamertag)', 'required' => true])
            ->add('submit', SubmitType::class, ['label' => 'Créer le match'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            // Find players by ID or gamertag
            $player1 = $playerRepository->find($data['player1']) ?? $playerRepository->findByGamertag($data['player1']);
            $player2 = $playerRepository->find($data['player2']) ?? $playerRepository->findByGamertag($data['player2']);

            if (!$player1 || !$player2) {
                $this->addFlash('error', 'Un ou plusieurs joueurs non trouvés');
                return $this->redirectToRoute('admin_tournament_matches', ['id' => $tournament->getId()]);
            }

            try {
                $matchService->createMatch($tournament, $player1, $player2);
                $this->addFlash('success', 'Match créé avec succès!');
                return $this->redirectToRoute('admin_tournament_matches', ['id' => $tournament->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('admin/tournament/create_match.html.twig', [
            'tournament' => $tournament,
            'form' => $form->createView(),
            'players' => $players,
        ]);
    }

    #[Route('/match/{id}/resolve-dispute', name: 'admin_match_resolve', methods: ['GET', 'POST'])]
    public function resolveDispute(int $id, Request $request, \App\Repository\MatchResultRepository $matchRepository, MatchService $matchService): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $match = $matchRepository->find($id);
        if (!$match || !$match->isNeedsModeration()) {
            throw $this->createNotFoundException('Match ou litige non trouvé');
        }

        $form = $this->createFormBuilder()
            ->add('result', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                'choices' => [
                    'Joueur 1 gagne' => \App\Entity\MatchResultType::WIN,
                    'Joueur 2 gagne' => \App\Entity\MatchResultType::LOSS,
                    'Égalité' => \App\Entity\MatchResultType::DRAW,
                ],
                'label' => 'Résultat du match',
            ])
            ->add('notes', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [
                'label' => 'Notes de modération',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, ['label' => 'Valider'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            try {
                $matchService->resolveDispute($match, $data['result']);
                if ($data['notes']) {
                    $match->setModerationNotes($data['notes']);
                }
                $this->addFlash('success', 'Litige résolu!');
                return $this->redirectToRoute('admin_match_moderation');
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('admin/match/resolve_dispute.html.twig', [
            'match' => $match,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/matches/moderation', name: 'admin_match_moderation', methods: ['GET'])]
    public function matchesModeration(\App\Repository\MatchResultRepository $matchRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $matches = $matchRepository->findBy(['needsModeration' => true], ['updatedAt' => 'DESC']);

        return $this->render('admin/match/moderation.html.twig', [
            'matches' => $matches,
        ]);
    }
}
