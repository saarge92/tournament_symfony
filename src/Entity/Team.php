<?php

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\UpdatedAtTrait;
use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

/**
 * @ORM\Entity(repositoryClass=TeamRepository::class)
 * @Gedmo\SoftDeleteable()
 * @ORM\Table(name="teams")
 * @ORM\HasLifecycleCallbacks
 */
class Team
{
    use CreatedAtTrait;
    use UpdatedAtTrait;
    use SoftDeleteableEntity;

    public function __construct(string $name, Division $division)
    {
        $this->setName($name);
        $this->setDivision($division);
        $this->tournamentResults = new ArrayCollection();
        $this->resultFinals = new ArrayCollection();
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private int $id_division;

    /**
     * @ORM\ManyToOne(targetEntity=Division::class, inversedBy="teams", fetch="LAZY", cascade={"persist"})
     * @ORM\JoinColumn(name="id_division",onDelete="SET NULL")
     */
    private ?Division $division;

    /**
     * @ORM\OneToMany(targetEntity=TournamentResult::class, mappedBy="team")
     */
    private $tournamentResults;

    /**
     * @ORM\OneToMany(targetEntity=ResultFinal::class, mappedBy="team")
     */
    private $resultFinals;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
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

    /**
     * @return Collection|TournamentResult[]
     */
    public function getTournamentResults(): Collection
    {
        return $this->tournamentResults;
    }

    public function addTournamentResult(TournamentResult $tournamentResult): self
    {
        if (!$this->tournamentResults->contains($tournamentResult)) {
            $this->tournamentResults[] = $tournamentResult;
            $tournamentResult->setTeam($this);
        }

        return $this;
    }

    public function removeTournamentResult(TournamentResult $tournamentResult): self
    {
        if ($this->tournamentResults->removeElement($tournamentResult)) {
            // set the owning side to null (unless already changed)
            if ($tournamentResult->getTeam() === $this) {
                $tournamentResult->setTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ResultFinal[]
     */
    public function getResultFinals(): Collection
    {
        return $this->resultFinals;
    }

    public function addResultFinal(ResultFinal $resultFinal): self
    {
        if (!$this->resultFinals->contains($resultFinal)) {
            $this->resultFinals[] = $resultFinal;
            $resultFinal->setTeam($this);
        }

        return $this;
    }

    public function removeResultFinal(ResultFinal $resultFinal): self
    {
        if ($this->resultFinals->removeElement($resultFinal)) {
            // set the owning side to null (unless already changed)
            if ($resultFinal->getTeam() === $this) {
                $resultFinal->setTeam(null);
            }
        }

        return $this;
    }
}
