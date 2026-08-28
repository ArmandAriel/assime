<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 100)]
    private string $name;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?int $idParent = null;

    /**
     * @var Collection<int, Listing>
     */
    #[ORM\OneToMany(targetEntity: Listing::class, mappedBy: 'category')]
    private Collection $listings;

    #[ORM\Column]
    private bool $Active;

    /**
     * @var Collection<int, AttributeOption>
     */
    #[ORM\OneToMany(targetEntity: AttributeOption::class, mappedBy: 'category')]
    private Collection $attributeOptions;

    /**
     * @var Collection<int, CategoryAttribute>
     */
    #[ORM\OneToMany(targetEntity: CategoryAttribute::class, mappedBy: 'category')]
    private Collection $categoryAttributes;

    public function __construct(
        string $name,
        bool $Active,
    ) {
        $this->name = $name;
        $this->listings = new ArrayCollection();
        $this->Active = $Active;
        $this->attributeOptions = new ArrayCollection();
        $this->categoryAttributes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getIdParent(): ?int
    {
        return $this->idParent;
    }

    public function setIdParent(?int $idParent): static
    {
        $this->idParent = $idParent;

        return $this;
    }

    /**
     * @return Collection<int, Listing>
     */
    public function getListings(): Collection
    {
        return $this->listings;
    }

    public function addListing(Listing $listing): static
    {
        if (!$this->listings->contains($listing)) {
            $this->listings->add($listing);
            $listing->setCategory($this);
        }

        return $this;
    }

    public function removeListing(Listing $listing): static
    {
        $this->listings->removeElement($listing);

        return $this;
    }

    public function isActive(): bool
    {
        return $this->Active;
    }

    public function setActive(bool $Active): static
    {
        $this->Active = $Active;

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
            $attributeOption->setCategory($this);
        }

        return $this;
    }

    public function removeAttributeOption(AttributeOption $attributeOption): static
    {
        if ($this->attributeOptions->removeElement($attributeOption)) {
            // set the owning side to null (unless already changed)
            if ($attributeOption->getCategory() === $this) {
                $attributeOption->setCategory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CategoryAttribute>
     */
    public function getCategoryAttributes(): Collection
    {
        return $this->categoryAttributes;
    }

    public function addCategoryAttribute(CategoryAttribute $categoryAttribute): static
    {
        if (!$this->categoryAttributes->contains($categoryAttribute)) {
            $this->categoryAttributes->add($categoryAttribute);
            $categoryAttribute->setCategory($this);
        }

        return $this;
    }

    public function removeCategoryAttribute(CategoryAttribute $categoryAttribute): static
    {
        if ($this->categoryAttributes->removeElement($categoryAttribute)) {
            // set the owning side to null (unless already changed)
            if ($categoryAttribute->getCategory() === $this) {
                $categoryAttribute->setCategory(null);
            }
        }

        return $this;
    }
}
