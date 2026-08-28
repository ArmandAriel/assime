<?php

namespace App\Entity;

use App\Repository\CategoryAttributeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryAttributeRepository::class)]
class CategoryAttribute
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private string $code;

    #[ORM\Column(length: 100)]
    private string $label;

    #[ORM\Column(length: 100)]
    private string $type;

    #[ORM\Column]
    private bool $isRequired;

    #[ORM\ManyToOne(inversedBy: 'categoryAttributes')]
    #[ORM\JoinColumn(nullable: false)]
    private Category $category;

    /**
     * @var Collection<int, AttributeOption>
     */
    #[ORM\OneToMany(targetEntity: AttributeOption::class, mappedBy: 'categoryAttribute')]
    private Collection $attributeOptions;

    public function __construct(string $code, string $label, string $type, bool $isRequired, Category $category)
    {
        $this->code = $code;
        $this->label = $label;
        $this->type = $type;
        $this->isRequired = $isRequired;
        $this->category = $category;
        $this->attributeOptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function setIsRequired(bool $isRequired): static
    {
        $this->isRequired = $isRequired;

        return $this;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, AttributeOption>
     */
    public function getAttributeOptions(): Collection
    {
        return $this->attributeOptions;
    }

    public function addAttributeOption(AttributeOption $attributeOption): static
    {
        if (!$this->attributeOptions->contains($attributeOption)) {
            $this->attributeOptions->add($attributeOption);
            $attributeOption->setCategoryAttribute($this);
        }

        return $this;
    }

    public function removeAttributeOption(AttributeOption $attributeOption): static
    {
        if ($this->attributeOptions->removeElement($attributeOption)) {
            // set the owning side to null (unless already changed)
            if ($attributeOption->getCategoryAttribute() === $this) {
                $attributeOption->setCategoryAttribute(null);
            }
        }

        return $this;
    }
}
