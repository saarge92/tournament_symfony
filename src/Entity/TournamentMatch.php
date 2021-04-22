<?php

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\UpdatedAtTrait;
use App\Repository\TournamentMatchRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

/**
 * @ORM\Entity(repositoryClass=TournamentMatchRepository::class)
 * @ORM\Table(name="matches")
 * @ORM\HasLifecycleCallbacks
 *
 */
class TournamentMatch
{
    use CreatedAtTrait;
    use UpdatedAtTrait;
    use SoftDeleteableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity=Division::class, inversedBy="tournamentMatches")
     */
    private ?Division $division;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="tournamentMatches", cascade={"persist"})
     * @ORM\JoinColumn(name="id_team_home", onDelete="SET NULL")
     */
    private ?Team $teamHome;

    /**
     * @ORM\Column(name="id_team_home", type="integer", nullable=true)
     */
    private ?int $idTeamHome;


    /**
     * @ORM\ManyToOne(targetEntity=Team::class, cascade={"persist"})
     * @ORM\JoinColumn(name="id_team_guest", onDelete="SET NULL")
     */
    private ?Team $teamGuest;

    /**
     * @ORM\Column(name="id_team_guest", type="integer", nullable=true)
     */
    private ?int $idTeamGuest;

    /**
     * @ORM\ManyToOne(targetEntity=Tournament::class, inversedBy="tournamentMatches")
     * @ORM\JoinColumn(name="tournament_id", onDelete="RESTRICT")
     */
    private ?Tournament $tournament;

    /**
     * @ORM\Column(name="tournament_id", type="integer", nullable=false)
     */
    private int $tournamentId;

    /**
     * @ORM\ManyToOne(targetEntity=Stage::class, inversedBy="tournamentMatches")
     * @ORM\JoinColumn(name="")
     */
    private ?Stage $stage;

    /**
     * @ORM\Column(type="integer", nullable=false, name="stage_id")
     */
    private int $stageId;

    /**
     * @ORM\Column(type="integer", name="count_goal_team_home")
     */
    private int $countGoalTeamHome;

    /**
     * @ORM\Column(type="integer", name="count_goal_team_guest")
     */
    private int $countGoalTeamGuest;

    public function __construct(
        Division $division,
        Team $teamHome,
        Team $teamGuest,
        int $countGoalTeamHome,
        int $countGoalTeamGuest,
        Stage $stage,
        Tournament $tournament
    ) {
        $this->setDivision($division);
        $this->setTeamHome($teamHome);
        $this->setTeamGuest($teamGuest);
        $this->setCountGoalTeamHome($countGoalTeamHome);
        $this->setCountGoalTeamGuest($countGoalTeamGuest);
        $this->setStage($stage);
        $this->setTournament($tournament);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStageId(): int
    {
        return $this->stageId;
    }

    public function getTournamentId(): int
    {
        return $this->tournamentId;
    }

    public function getIdTeamHome(): ?int
    {
        return $this->idTeamHome;
    }

    public function getIdTeamGuest(): ?int
    {
        return $this->idTeamGuest;
    }

    public function getDivision(): ?Division
    {
        return $this->division;
    }

    public function setDivision(?Division $division): self
    {
        $this->division = $division;

        return $this;
    }

    public function getTeamHome(): ?Team
    {
        return $this->teamHome;
    }

    public function setTeamHome(?Team $teamHome): self
    {
        $this->teamHome = $teamHome;

        return $this;
    }

    public function getTeamGuest(): ?Team
    {
        return $this->teamGuest;
    }

    public function setTeamGuest(?Team $teamGuest): self
    {
        $this->teamGuest = $teamGuest;

        return $this;
    }

    public function getTournament(): ?Tournament
    {
        return $this->tournament;
    }

    public function setTournament(?Tournament $tournament): self
    {
        $this->tournament = $tournament;

        return $this;
    }

    public function getStage(): ?Stage
    {
        return $this->stage;
    }

    public function setStage(?Stage $stage): self
    {
        $this->stage = $stage;

        return $this;
    }

    public function getCountGoalTeamHome(): ?int
    {
        return $this->countGoalTeamHome;
    }

    public function setCountGoalTeamHome(int $countGoalTeamHome): self
    {
        $this->countGoalTeamHome = $countGoalTeamHome;

        return $this;
    }

    public function getCountGoalTeamGuest(): ?int
    {
        return $this->countGoalTeamGuest;
    }

    public function setCountGoalTeamGuest(int $countGoalTeamGuest): self
    {
        $this->countGoalTeamGuest = $countGoalTeamGuest;

        return $this;
    }
}
