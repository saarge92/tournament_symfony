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

    /**
     * @return TournamentMatch[]
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getMatchesByTeamIdAndTournament(int $idTeam, int $tournamentId, int $stageId): array
    {
//        $sql = "
//            SELECT * from matches where (id_team_home = :team_home OR id_team_guest = :team_guest) AND (id_tournament = :id_tournament and id_stage = :id_stage)
//            order by id asc
//        ";
//        $query = $this->getEntityManager()->getConnection()->prepare($sql);
//        $query->bindParam("team_home", $idTeam);
//        $query->bindParam("team_guest", $idTeam);
//        $query->bindParam("id_tournament", $tournamentId);
//        $query->bindParam("id_stage", $stageId);
//        return $query->executeQuery()->fetchAllAssociative();
        $statement = $this->_em->createQuery(
            'select tr from App\Entity\TournamentMatch tr where 
            (tr.idTeamHome = :team_home OR tr.idTeamGuest = :team_guest) AND (tr.tournamentId = :id_tournament and tr.stageId = :id_stage) 
            order by tr.id asc'
        );
        $statement->setParameter("team_home", $idTeam);
        $statement->setParameter("team_guest", $idTeam);
        $statement->setParameter("id_tournament", $tournamentId);
        $statement->setParameter("id_stage", $stageId);
        return $statement->getArrayResult();
    }
}
