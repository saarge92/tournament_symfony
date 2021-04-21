<?php

namespace App\Repository;

use App\Entity\Division;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Division|null find($id, $lockMode = null, $lockVersion = null)
 * @method Division|null findOneBy(array $criteria, array $orderBy = null)
 * @method Division[]    findAll()
 * @method Division[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DivisionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Division::class);
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     */
    public function save(Division $division): void
    {
        $this->_em->persist($division);
        $this->_em->flush();
    }
}
