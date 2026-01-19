<?php

namespace App\Repository;

use App\Entity\MatchResult;
use App\Entity\MatchStatus;
use App\Entity\Player;
use App\Entity\Tournament;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MatchResult>
 */
class MatchResultRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MatchResult::class);
    }

    public function findByTournament(Tournament $tournament)
    {
        return $this->findBy(['tournament' => $tournament], ['scheduledDate' => 'ASC']);
    }

    public function findPendingMatches(Tournament $tournament)
    {
        return $this->findBy(['tournament' => $tournament, 'status' => MatchStatus::PENDING], ['scheduledDate' => 'ASC']);
    }

    public function findFinishedMatches(Tournament $tournament)
    {
        return $this->findBy(['tournament' => $tournament, 'status' => MatchStatus::FINISHED], ['playedDate' => 'DESC']);
    }

    public function findPlayerMatches(Player $player, Tournament $tournament = null)
    {
        $qb = $this->createQueryBuilder('m')
            ->where('m.player1 = :player OR m.player2 = :player')
            ->setParameter('player', $player);

        if ($tournament !== null) {
            $qb->andWhere('m.tournament = :tournament')
                ->setParameter('tournament', $tournament);
        }

        return $qb->orderBy('m.scheduledDate', 'DESC')->getQuery()->getResult();
    }

    /**
     * Find if two players have already played in a tournament
     */
    public function findExistingMatch(Tournament $tournament, Player $player1, Player $player2, ?int $excludeId = null): ?MatchResult
    {
        $qb = $this->createQueryBuilder('m')
            ->where('m.tournament = :tournament')
            ->setParameter('tournament', $tournament)
            ->andWhere(
                '(m.player1 = :player1 AND m.player2 = :player2) OR (m.player1 = :player2 AND m.player2 = :player1)'
            )
            ->setParameter('player1', $player1)
            ->setParameter('player2', $player2);

        if ($excludeId !== null) {
            $qb->andWhere('m.id != :excludeId')->setParameter('excludeId', $excludeId);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findMatchesPendingModeration(Tournament $tournament = null)
    {
        $qb = $this->createQueryBuilder('m')
            ->where('m.needsModeration = true');

        if ($tournament !== null) {
            $qb->andWhere('m.tournament = :tournament')
                ->setParameter('tournament', $tournament);
        }

        return $qb->orderBy('m.updatedAt', 'DESC')->getQuery()->getResult();
    }

    public function findPlayersPendingMatches(Player $player)
    {
        return $this->createQueryBuilder('m')
            ->where('(m.player1 = :player OR m.player2 = :player)')
            ->andWhere('m.status = :status')
            ->setParameter('player', $player)
            ->setParameter('status', MatchStatus::PENDING)
            ->orderBy('m.scheduledDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
