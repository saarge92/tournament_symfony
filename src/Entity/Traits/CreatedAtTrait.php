<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait CreatedAtTrait
{
    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private \DateTimeInterface $createdAt;

    /**
     * @ORM\PrePersist()
     */
    public function beforeCreate(): void
    {
        $this->createdAt = new \DateTime('now');
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

}