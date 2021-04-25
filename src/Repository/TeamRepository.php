<?php

namespace App\Repository;

use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Team|null find($id, $lockMode = null, $lockVersion = null)
 * @method Team|null findOneBy(array $criteria, array $orderBy = null)
 * @method Team[]    findAll()
 * @method Team[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }

    /**
     * @return Team[]
     */
    public function getTeamByDivisionId(int $divisionId): array
    {
        return $this->findBy(['idDivision' => $divisionId], ['id' => 'asc']);
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getRandomTeam(): ?Team
    {
        return $this->createQueryBuilder('q')
            ->addSelect('RAND() as HIDDEN rand')
            ->addOrderBy('rand')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByIdNotEqual(int $idValue): array
    {
        $qb = $this->createQueryBuilder('t');
        $qb->where($qb->expr()->neq('t.id', ':id'));
        $qb->setParameter("id", $idValue);
        return $qb->getQuery()
            ->getArrayResult();
    }
}
