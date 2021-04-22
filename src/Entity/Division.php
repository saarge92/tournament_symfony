<?php

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\UpdatedAtTrait;
use App\Repository\DivisionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

/**
 * @ORM\Entity(repositoryClass=DivisionRepository::class)
 * @Gedmo\SoftDeleteable()
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="divisions")
 */
class Division
{
    use SoftDeleteableEntity;
    use CreatedAtTrait;
    use UpdatedAtTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private string $name;

    /**
     * @ORM\OneToMany (targetEntity=Team::class, mappedBy="division", fetch="EXTRA_LAZY")
     */
    private ?PersistentCollection $teams;

    /**
     * @ORM\OneToMany(targetEntity=TournamentMatch::class, mappedBy="id_division")
     */
    private ?PersistentCollection $tournamentMatches;

    public function __construct(string $name)
    {
        $this->setName($name);
        $this->teams = null;
        $this->tournamentMatches = null;
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

    /**
     * @return PersistentCollection|null
     */
    public function getTeams(): ?PersistentCollection
    {
        return $this->teams;
    }

    public function addTournamentMatch(TournamentMatch $tournamentMatch): self
    {
        if (!$this->tournamentMatches->contains($tournamentMatch)) {
            $this->tournamentMatches[] = $tournamentMatch;
            $tournamentMatch->setIdDivision($this);
        }

        return $this;
    }

    public function removeTournamentMatch(TournamentMatch $tournamentMatch): self
    {
        if ($this->tournamentMatches->removeElement($tournamentMatch)) {
            // set the owning side to null (unless already changed)
            if ($tournamentMatch->getDivision() === $this) {
                $tournamentMatch->setDivision(null);
            }
        }

        return $this;
    }
}
