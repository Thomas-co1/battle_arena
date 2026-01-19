<?php

namespace App\Controller;

use App\Repository\MatchResultRepository;
use App\Repository\TournamentRepository;
use App\Service\TournamentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/tournament')]
class TournamentController extends AbstractController
{
    #[Route('', name: 'app_tournament_list', methods: ['GET'])]
    public function list(TournamentRepository $tournamentRepository): Response
    {
        $upcomingTournaments = $tournamentRepository->findUpcoming();
        $activeTournaments = $tournamentRepository->findActive();
        $finishedTournaments = $tournamentRepository->findFinished();

        return $this->render('tournament/list.html.twig', [
            'upcoming' => $upcomingTournaments,
            'active' => $activeTournaments,
            'finished' => $finishedTournaments,
        ]);
    }

    #[Route('/{id}', name: 'app_tournament_show', methods: ['GET'])]
    public function show(int $id, TournamentRepository $tournamentRepository, MatchResultRepository $matchRepository, TournamentService $tournamentService): Response
    {
        $tournament = $tournamentRepository->find($id);

        if (!$tournament) {
            throw $this->createNotFoundException('Tournoi non trouvé');
        }

        $matches = $matchRepository->findByTournament($tournament);
        $stats = $tournamentService->getTournamentStats($tournament);
        $players = $tournamentService->getTournamentPlayers($tournament);

        return $this->render('tournament/show.html.twig', [
            'tournament' => $tournament,
            'matches' => $matches,
            'stats' => $stats,
            'players' => $players,
        ]);
    }
}
