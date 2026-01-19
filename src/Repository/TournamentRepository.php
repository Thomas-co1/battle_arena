<?php

namespace App\Repository;

use App\Entity\Tournament;
use App\Entity\TournamentStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tournament>
 */
class TournamentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tournament::class);
    }

    public function findActive()
    {
        return $this->findBy(['status' => TournamentStatus::ACTIVE], ['startDate' => 'DESC']);
    }

    public function findUpcoming()
    {
        return $this->findBy(['status' => TournamentStatus::PENDING], ['startDate' => 'ASC']);
    }

    public function findFinished()
    {
        return $this->findBy(['status' => TournamentStatus::FINISHED], ['endDate' => 'DESC']);
    }

    public function findByStatus(TournamentStatus $status)
    {
        return $this->findBy(['status' => $status]);
    }
}
