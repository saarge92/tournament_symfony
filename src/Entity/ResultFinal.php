<?php

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\UpdatedAtTrait;
use App\Repository\ResultFinalRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

/**
 * @ORM\Entity(repositoryClass=ResultFinalRepository::class)
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable()
 * @ORM\Table(name="result_finals")
 */
class ResultFinal implements \JsonSerializable
{
    use CreatedAtTrait;
    use UpdatedAtTrait;
    use SoftDeleteableEntity;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="resultFinals")
     * @ORM\JoinColumn(name="id_team", onDelete="RESTRICT")
     */
    private Team $team;

    /**
     * @Column(type="integer", nullable=false, name="id_team")
     * @Id
     */
    private int $idTeam;

    /**
     * @ORM\ManyToOne(targetEntity=Tournament::class, inversedBy="resultFinals")
     * @ORM\JoinColumn(name="id_tournament", onDelete="RESTRICT")
     */
    private Tournament $tournament;

    /**
     * @Column(name="id_tournament", type="integer")
     * @Id
     */
    private int $idTournament;

    /**
     * @ORM\Column(type="integer")
     */
    private int $place;

    public function __construct(Team $team, Tournament $tournament, int $place)
    {
        $this->setTeam($team);
        $this->setTournament($tournament);
        $this->setPlace($place);
    }

    public function getIdTeam(): int
    {
        return $this->idTeam;
    }

    public function setIdTeam(int $idTeam): void
    {
        $this->idTeam = $idTeam;
    }

    public function getIdTournament(): int
    {
        return $this->idTournament;
    }

    public function setIdTournament(int $idTournament): void
    {
        $this->idTournament = $idTournament;
    }

    public function getTeam(): Team
    {
        return $this->team;
    }

    public function setTeam(Team $team): self
    {
        $this->team = $team;
        $this->setIdTeam($team->getId());
        return $this;
    }

    public function getTournament(): Tournament
    {
        return $this->tournament;
    }

    public function setTournament(Tournament $tournament): self
    {
        $this->tournament = $tournament;
        $this->setIdTournament($tournament->getId());
        return $this;
    }

    public function getPlace(): ?int
    {
        return $this->place;
    }

    public function setPlace(int $place): self
    {
        $this->place = $place;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'id_team' => $this->getIdTeam(),
            'id_tournament' => $this->getIdTournament(),
            'place' => $this->getPlace()
        ];
    }
}
