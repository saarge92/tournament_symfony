<?php

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\UpdatedAtTrait;
use App\Repository\TournamentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

/**
 * @ORM\Entity(repositoryClass=TournamentRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="tournaments")
 * @Gedmo\SoftDeleteable()
 */
class Tournament
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
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\OneToMany(targetEntity=TournamentMatch::class, mappedBy="id_tournament", fetch="LAZY")
     */
    private ?PersistentCollection $tournamentMatches;

    /**
     * @ORM\OneToMany(targetEntity=TournamentResult::class, mappedBy="tournament", fetch="LAZY")
     */
    private ?PersistentCollection $tournamentResults;

    /**
     * @ORM\OneToMany(targetEntity=ResultFinal::class, mappedBy="Tournament", fetch="LAZY")
     */
    private ?PersistentCollection $resultFinals;

    public function __construct(string $name)
    {
        $this->setName($name);
        $this->tournamentMatches = null;
        $this->tournamentResults = null;
        $this->resultFinals = null;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|TournamentMatch[]
     */
    public function getTournamentMatches(): Collection
    {
        return $this->tournamentMatches;
    }

    public function addTournamentMatch(TournamentMatch $tournamentMatch): self
    {
        if (!$this->tournamentMatches->contains($tournamentMatch)) {
            $this->tournamentMatches[] = $tournamentMatch;
            $tournamentMatch->setTournament($this);
        }

        return $this;
    }

    public function removeTournamentMatch(TournamentMatch $tournamentMatch): self
    {
        if ($this->tournamentMatches->removeElement($tournamentMatch)) {
            // set the owning side to null (unless already changed)
            if ($tournamentMatch->getTournament() === $this) {
                $tournamentMatch->setTournament(null);
            }
        }

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
            $tournamentResult->setTournament($this);
        }

        return $this;
    }

    public function removeTournamentResult(TournamentResult $tournamentResult): self
    {
        if ($this->tournamentResults->removeElement($tournamentResult)) {
            // set the owning side to null (unless already changed)
            if ($tournamentResult->getTournament() === $this) {
                $tournamentResult->setTournament(null);
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
            $resultFinal->setTournament($this);
        }

        return $this;
    }

    public function removeResultFinal(ResultFinal $resultFinal): self
    {
        if ($this->resultFinals->removeElement($resultFinal)) {
            // set the owning side to null (unless already changed)
            if ($resultFinal->getTournament() === $this) {
                $resultFinal->setTournament(null);
            }
        }

        return $this;
    }
}
