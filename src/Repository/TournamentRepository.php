<?php

namespace App\Repository;

use App\Entity\Tournament;
use App\Entity\TournamentResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

use function Doctrine\ORM\QueryBuilder;

/**
 * @method Tournament|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tournament|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tournament[]    findAll()
 * @method Tournament[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TournamentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tournament::class);
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     */
    public function createTournament(Tournament $tournament): void
    {
        $this->_em->persist($tournament);
        $this->_em->flush();
    }

    public function getUnsettledTournaments(): Tournament
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        return $queryBuilder->select('t')->from(Tournament::class, 't')->where(
            $queryBuilder->expr()->not(
                $queryBuilder->expr()->exists(
                    $queryBuilder->select('tr')->from(TournamentResult::class, 'tr')
                        ->where('tr.id_tournament = t.id')
                )
            )
        )->getQuery()->getResult();
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getRandomTournament(): ?Tournament
    {
        return $this->createQueryBuilder('q')
            ->addSelect('RAND() as HIDDEN rand')
            ->addOrderBy('rand')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
