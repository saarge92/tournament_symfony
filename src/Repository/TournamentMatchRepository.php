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

    public function getMatchesByTournamentForStages(int $tournamentId, array $stages): array
    {
        $queryBuilder = $this->_em->createQuery(
            "
                select tr as result_match, t1.name as team_home_name, t2.name as team_guest_name, t1.id_division as team_home_division, 
                t2.id_division as team_guest_division
                from App\Entity\TournamentMatch tr inner join
                App\Entity\Team t1 WITH t1.id = tr.idTeamHome 
                inner join App\Entity\Team t2
                WITH t2.id = tr.idTeamGuest
                where tr.stageId in (:stage) order by t1.id, t2.id
        "
        );
        $queryBuilder->setParameter("stage", $stages);
        return $queryBuilder->getArrayResult();
    }
}
