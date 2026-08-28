<?php

namespace App\Entity;

use App\Repository\AttributeOptionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AttributeOptionRepository::class)]
class AttributeOption
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $value = null;

    #[ORM\Column(length: 100)]
    private ?string $label = null;

    #[ORM\ManyToOne(inversedBy: 'attributeOptions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CategoryAttribute $categoryAttribute = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getCategoryAttribute(): ?CategoryAttribute
    {
        return $this->categoryAttribute;
    }

    public function setCategoryAttribute(?CategoryAttribute $categoryAttribute): static
    {
        $this->categoryAttribute = $categoryAttribute;

        return $this;
    }
}
