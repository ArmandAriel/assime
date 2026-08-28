<?php

namespace App\Entity;

use App\Repository\ListingAttributeValueRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ListingAttributeValueRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_LISTING_ATTRIBUTE', columns: ['listing_id', 'category_attribute_id'])]
class ListingAttributeValue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $valueText = null;

    #[ORM\Column(nullable: true)]
    private ?float $valueNumber = null;

    #[ORM\Column(nullable: true)]
    private ?bool $valueBoolean = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $valueDate = null;

    #[ORM\ManyToOne(inversedBy: 'listingAttributeValues')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Listing $listing = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?CategoryAttribute $categoryAttribute = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValueText(): ?string
    {
        return $this->valueText;
    }

    public function setValueText(?string $valueText): static
    {
        $this->valueText = $valueText;

        return $this;
    }

    public function getValueNumber(): ?float
    {
        return $this->valueNumber;
    }

    public function setValueNumber(?float $valueNumber): static
    {
        $this->valueNumber = $valueNumber;

        return $this;
    }

    public function isValueBoolean(): ?bool
    {
        return $this->valueBoolean;
    }

    public function setValueBoolean(?bool $valueBoolean): static
    {
        $this->valueBoolean = $valueBoolean;

        return $this;
    }

    public function getValueDate(): ?\DateTime
    {
        return $this->valueDate;
    }

    public function setValueDate(?\DateTime $valueDate): static
    {
        $this->valueDate = $valueDate;

        return $this;
    }

    public function getListing(): ?Listing
    {
        return $this->listing;
    }

    public function setListing(?Listing $listing): static
    {
        $this->listing = $listing;

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
