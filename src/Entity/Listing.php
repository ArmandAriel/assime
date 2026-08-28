<?php

namespace App\Entity;

use App\Enums\ListingStatus;
use App\Repository\ListingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ListingRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Listing implements TimestampableInterface
{
    use TimestampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150, nullable: false)]
    private string $title;

    #[ORM\Column(length: 255, nullable: false)]
    private string $description;

    #[ORM\Column(type: Types::FLOAT, precision: 8, scale: 2)]
    private ?float $price = null;

    #[ORM\Column(length: 255, nullable: false)]
    private string $localisationDetails ;

    #[ORM\ManyToOne(inversedBy: 'listings')]
    #[ORM\JoinColumn(nullable: false)]
    private Category $category;

    /**
     * @var Collection<int, Image>
     */
    #[ORM\OneToMany(targetEntity: Image::class, mappedBy: 'listing')]
    private Collection $images;

    #[ORM\ManyToOne(inversedBy: 'listings')]
    #[ORM\JoinColumn(nullable: false)]
    private City $city;

    #[ORM\Column(enumType: ListingStatus::class)]
    private ListingStatus $status;

    /**
     * @var Collection<int, ListingAttributeValue>
     */
    #[ORM\OneToMany(targetEntity: ListingAttributeValue::class, mappedBy: 'listing')]
    private Collection $listingAttributeValues;

    #[ORM\ManyToOne(inversedBy: 'listings')]
    #[ORM\JoinColumn(nullable: false)]
    private User $owner;

    public function __construct(
        string $title,
        string $description,
        float $price,
        string $localisationDetails,
        Category $category,
        City $city,
        User $owner,
        ListingStatus $status = ListingStatus::Draft
    ) {
        $this->images = new ArrayCollection();
        $this->title = $title;
        $this->description = $description;
        $this->price = $price;
        $this->localisationDetails = $localisationDetails;
        $this->category = $category;
        $this->city = $city;
        $this->owner = $owner;
        $this->status = $status;
        $this->createdAt = new \DateTime();
        $this->listingAttributeValues = new ArrayCollection();
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getLocalisationDetails(): ?string
    {
        return $this->localisationDetails;
    }

    public function setLocalisationDetails(string $localisationDetails): static
    {
        $this->localisationDetails = $localisationDetails;

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
     * @return Collection<int, Image>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setListing($this);
        }

        return $this;
    }

    public function removeImage(Image $image): static
    {
        if ($this->images->removeElement($image)) {
            if ($image->getListing() === $this) {
                $image->setListing(null);
            }
        }

        return $this;
    }

    public function getCity(): City
    {
        return $this->city;
    }

    public function setCity(City $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getStatus(): ListingStatus
    {
        return $this->status;
    }

    public function setStatus(ListingStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, ListingAttributeValue>
     */
    public function getListingAttributeValues(): Collection
    {
        return $this->listingAttributeValues;
    }

    public function addListingAttributeValue(ListingAttributeValue $listingAttributeValue): static
    {
        if (!$this->listingAttributeValues->contains($listingAttributeValue)) {
            $this->listingAttributeValues->add($listingAttributeValue);
            $listingAttributeValue->setListing($this);
        }

        return $this;
    }

    public function removeListingAttributeValue(ListingAttributeValue $listingAttributeValue): static
    {
        if ($this->listingAttributeValues->removeElement($listingAttributeValue)) {
            // set the owning side to null (unless already changed)
            if ($listingAttributeValue->getListing() === $this) {
                $listingAttributeValue->setListing(null);
            }
        }

        return $this;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }
}
