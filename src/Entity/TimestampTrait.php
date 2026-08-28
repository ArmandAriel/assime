<?php

namespace App\Entity;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

trait TimestampTrait
{
    #[ORM\Column(type: 'datetime', nullable: false)]
    protected DateTimeInterface $createdAt;
    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?DateTimeInterface $updatedAt = null;


    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        if ($createdAt instanceof DateTimeImmutable) {
            $createdAt = DateTime::createFromImmutable($createdAt);
        }

        $this->createdAt = $createdAt;
        return $this;
    }

    public function setUpdatedAt(?DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }
    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }


}
