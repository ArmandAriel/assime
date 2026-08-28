<?php

namespace App\Entity;

use DateTimeInterface;

interface TimestampableInterface
{
    public function setCreatedAt(DateTimeInterface $createdAt): self;
    public function setUpdatedAt(?DateTimeInterface $updatedAt): self;

    public function getCreatedAt(): DateTimeInterface;
    public function getUpdatedAt(): ?DateTimeInterface;


}
