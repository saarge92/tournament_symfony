<?php

namespace App\Repository;

use App\Entity\TournamentResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TournamentResult|null find($id, $lockMode = null, $lockVersion = null)
 * @method TournamentResult|null findOneBy(array $criteria, array $orderBy = null)
 * @method TournamentResult[]    findAll()
 * @method TournamentResult[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TournamentResultRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TournamentResult::class);
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(TournamentResult $tournamentResult): void
    {
        $this->_em->persist($tournamentResult);
        $this->_em->flush();
    }
}
