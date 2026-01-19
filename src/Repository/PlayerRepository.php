<?php

namespace App\Repository;

use App\Entity\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Player>
 */
class PlayerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Player::class);
    }

    public function findByGamertag(string $gamertag): ?Player
    {
        return $this->findOneBy(['gamertag' => $gamertag]);
    }

    public function findTopPlayers(int $limit = 10)
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.wins', 'DESC')
            ->addOrderBy('p.losses', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findBySkillLevel(int $skillLevel)
    {
        return $this->findBy(['skillLevel' => $skillLevel]);
    }
}
