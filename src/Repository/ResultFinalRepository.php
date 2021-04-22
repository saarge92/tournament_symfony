<?php

namespace App\Repository;

use App\Entity\ResultFinal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ResultFinal|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResultFinal|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResultFinal[]    findAll()
 * @method ResultFinal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResultFinalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResultFinal::class);
    }

    /**
     * @return ResultFinal[]
     */
    public function getFinaleResultByTournamentId(int $idTournament): array
    {
        return $this->findBy(['idTournament' => $idTournament], ['place' => 'desc']);
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     */
    public function save(ResultFinal $resultFinal): void
    {
        $this->_em->persist($resultFinal);
        $this->_em->flush();
    }
}
