<?php

namespace App\Service;

use App\Entity\MatchResult;
use App\Entity\MatchResultType;
use App\Entity\MatchStatus;
use App\Entity\Player;
use App\Entity\Tournament;
use App\Repository\MatchResultRepository;
use Doctrine\ORM\EntityManagerInterface;

class MatchService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MatchResultRepository $matchResultRepository,
    ) {
    }

    /**
     * Create a match between two players
     */
    public function createMatch(Tournament $tournament, Player $player1, Player $player2, ?\DateTimeImmutable $scheduledDate = null): MatchResult
    {
        // Vérifier que les deux joueurs ne se sont pas déjà affrontés
        $existingMatch = $this->matchResultRepository->findExistingMatch($tournament, $player1, $player2);
        if ($existingMatch !== null) {
            throw new \Exception('Ces deux joueurs se sont déjà affrontés dans ce tournoi');
        }

        $match = new MatchResult();
        $match->setTournament($tournament);
        $match->setPlayer1($player1);
        $match->setPlayer2($player2);
        $match->setScheduledDate($scheduledDate);
        $match->setStatus(MatchStatus::PENDING);

        $this->entityManager->persist($match);
        $this->entityManager->flush();

        return $match;
    }

    /**
     * Submit result for a player
     */
    public function submitResult(MatchResult $match, Player $player, MatchResultType $result): void
    {
        if ($player === $match->getPlayer1()) {
            $match->setPlayer1Result($result);
        } elseif ($player === $match->getPlayer2()) {
            $match->setPlayer2Result($result);
        } else {
            throw new \Exception('Le joueur ne participe pas à ce match');
        }

        // Vérifier si les deux joueurs ont soumis les résultats
        if ($match->hasResultsSubmitted()) {
            if ($match->resultsAreConsistent()) {
                // Les résultats sont cohérents, finaliser le match
                $this->finalizeMatch($match);
            } else {
                // Les résultats sont incohérents, marquer pour modération
                $match->setNeedsModeration(true);
            }
        }

        $match->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();
    }

    /**
     * Finalize a match and update player stats
     */
    public function finalizeMatch(MatchResult $match): void
    {
        if (!$match->hasResultsSubmitted() || !$match->resultsAreConsistent()) {
            throw new \Exception('Le match n\'a pas de résultats cohérents');
        }

        $match->setStatus(MatchStatus::FINISHED);
        $match->setPlayedDate(new \DateTimeImmutable());
        $match->setNeedsModeration(false);

        $winner = $match->getWinner();

        // Mettre à jour les statistiques
        if ($winner === $match->getPlayer1()) {
            $match->getPlayer1()->addWin();
            $match->getPlayer2()->addLoss();
        } elseif ($winner === $match->getPlayer2()) {
            $match->getPlayer2()->addWin();
            $match->getPlayer1()->addLoss();
        }
        // Les égalités ne modifient pas les stats

        $this->entityManager->flush();
    }

    /**
     * Admin resolves a disputed match
     */
    public function resolveDispute(MatchResult $match, MatchResultType $winnerResult): void
    {
        if ($winnerResult === MatchResultType::DRAW) {
            $match->setPlayer1Result(MatchResultType::DRAW);
            $match->setPlayer2Result(MatchResultType::DRAW);
        } else {
            if ($winnerResult === MatchResultType::WIN) {
                $match->setPlayer1Result(MatchResultType::WIN);
                $match->setPlayer2Result(MatchResultType::LOSS);
            } else {
                $match->setPlayer1Result(MatchResultType::LOSS);
                $match->setPlayer2Result(MatchResultType::WIN);
            }
        }

        $this->finalizeMatch($match);
        $match->setModerationNotes('Résultat validé par l\'administrateur');
    }

    /**
     * Cancel a match
     */
    public function cancelMatch(MatchResult $match, string $reason = ''): void
    {
        $match->setStatus(MatchStatus::CANCELLED);
        if ($reason) {
            $match->setModerationNotes($reason);
        }
        $this->entityManager->flush();
    }

    /**
     * Mark match as in progress
     */
    public function startMatch(MatchResult $match): void
    {
        if ($match->getStatus() !== MatchStatus::PENDING) {
            throw new \Exception('Seul un match en attente peut être lancé');
        }
        $match->setStatus(MatchStatus::IN_PROGRESS);
        $this->entityManager->flush();
    }
}
