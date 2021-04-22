<?php

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\UpdatedAtTrait;
use App\Repository\TournamentResultRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

/**
 * @ORM\Entity(repositoryClass=TournamentResultRepository::class)
 * @Gedmo\SoftDeleteable()
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="tournament_results")
 */
class TournamentResult
{
    use CreatedAtTrait;
    use UpdatedAtTrait;
    use SoftDeleteableEntity;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="tournamentResults", cascade={"persist"})
     * @ORM\JoinColumn(name="id_team", onDelete="RESTRICT")
     */
    private Team $team;

    /**
     * @Column(type="integer", nullable=false, name="id_team")
     * @Id
     */
    private int $idTeam;

    /**
     * @ORM\ManyToOne(targetEntity=Tournament::class, inversedBy="tournamentResults")
     * @ORM\JoinColumn(name="id_tournament", onDelete="RESTRICT")
     */
    private Tournament $tournament;

    /**
     * @Column(name="id_tournament", type="integer")
     * @Id
     */
    private int $idTournament;

    /**
     * @ORM\Column(type="float")
     */
    private float $points;

    public function __construct(Tournament $tournament, Team $team, int $point)
    {
        $this->setTournament($tournament);
        $this->setTeam($team);
        $this->setPoints($point);
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    public function getTournament(): ?Tournament
    {
        return $this->tournament;
    }

    public function setTournament(Tournament $tournament): self
    {
        $this->tournament = $tournament;

        return $this;
    }

    public function getPoints(): ?float
    {
        return $this->points;
    }

    public function setPoints(float $points): self
    {
        $this->points = $points;

        return $this;
    }
}
