<?php

namespace App\Repository;

use App\Entity\TournamentMatch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TournamentMatch|null find($id, $lockMode = null, $lockVersion = null)
 * @method TournamentMatch|null findOneBy(array $criteria, array $orderBy = null)
 * @method TournamentMatch[]    findAll()
 * @method TournamentMatch[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TournamentMatchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TournamentMatch::class);
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(TournamentMatch $tournamentMatch): void
    {
        $this->_em->persist($tournamentMatch);
        $this->_em->flush();
    }

}
