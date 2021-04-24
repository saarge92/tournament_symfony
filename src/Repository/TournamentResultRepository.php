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


    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getTournamentResultByTournamentId(int $tournamentId): array
    {
        $sql = "
        select * from divisions d inner join teams t on t.id_division = d.id
        inner join tournament_results tr on tr.id_team = t.id where tr.id_tournament = :tournament_id
        order by d.id, t.id
        ";
        $statement = $this->getEntityManager()->getConnection()->prepare($sql);
        $statement->bindParam('tournament_id', $tournamentId);
        return $statement->executeQuery()->fetchAllAssociative();
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getRandomResult(): ?TournamentResult
    {
        return $this->createQueryBuilder('q')
            ->addSelect('RAND() as HIDDEN rand')
            ->addOrderBy('rand')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
