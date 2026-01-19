<?php

namespace App\Service;

use App\Entity\Tournament;
use App\Entity\TournamentStatus;
use Doctrine\ORM\EntityManagerInterface;

class TournamentService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Create a new tournament
     */
    public function createTournament(string $name, \DateTimeImmutable $startDate, ?string $description = null, int $maxPlayers = 8): Tournament
    {
        $tournament = new Tournament();
        $tournament->setName($name);
        $tournament->setStartDate($startDate);
        $tournament->setDescription($description);
        $tournament->setMaxPlayers($maxPlayers);
        $tournament->setStatus(TournamentStatus::PENDING);

        $this->entityManager->persist($tournament);
        $this->entityManager->flush();

        return $tournament;
    }

    /**
     * Start a tournament
     */
    public function startTournament(Tournament $tournament): void
    {
        if ($tournament->getStatus() !== TournamentStatus::PENDING) {
            throw new \Exception('Seul un tournoi en attente peut être lancé');
        }

        $tournament->setStatus(TournamentStatus::ACTIVE);
        $this->entityManager->flush();
    }

    /**
     * Finish a tournament
     */
    public function finishTournament(Tournament $tournament): void
    {
        if ($tournament->getStatus() !== TournamentStatus::ACTIVE) {
            throw new \Exception('Seul un tournoi actif peut être terminé');
        }

        $tournament->setStatus(TournamentStatus::FINISHED);
        $tournament->setEndDate(new \DateTimeImmutable());
        $this->entityManager->flush();
    }

    /**
     * Cancel a tournament
     */
    public function cancelTournament(Tournament $tournament): void
    {
        $tournament->setStatus(TournamentStatus::CANCELLED);
        $this->entityManager->flush();
    }

    /**
     * Get tournament statistics
     */
    public function getTournamentStats(Tournament $tournament): array
    {
        $matches = $tournament->getMatches();
        $finishedMatches = $matches->filter(fn ($m) => $m->isFinished());
        $pendingMatches = $matches->filter(fn ($m) => $m->isPending());

        return [
            'total_matches' => $matches->count(),
            'finished_matches' => $finishedMatches->count(),
            'pending_matches' => $pendingMatches->count(),
            'players' => $this->getTournamentPlayers($tournament)->count(),
        ];
    }

    /**
     * Get all unique players in a tournament
     */
    public function getTournamentPlayers(Tournament $tournament)
    {
        $players = new \Doctrine\Common\Collections\ArrayCollection();
        foreach ($tournament->getMatches() as $match) {
            if (!$players->contains($match->getPlayer1())) {
                $players->add($match->getPlayer1());
            }
            if (!$players->contains($match->getPlayer2())) {
                $players->add($match->getPlayer2());
            }
        }
        return $players;
    }
}
