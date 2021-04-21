<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait UpdatedAtTrait
{
    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private \DateTimeInterface $updatedAt;

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function beforeUpdate(): void
    {
        $this->updatedAt = new \DateTime('now');
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }
}