<?php

namespace App\Dto\Request\Listing;

use App\Enums\ListingStatus;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateListingRequestDto
{
    public function __construct(
        #[Assert\Positive(message: "L'identifiant de l'annonce est requis")]
        public int $id,
        #[Assert\NotBlank(message: 'Le titre est requis')]
        #[Assert\Length(max: 150, maxMessage: 'Le titre ne peut pas depasser {{ limit }} caracteres')]
        public string $title,
        #[Assert\NotBlank(message: 'La description est requise')]
        #[Assert\Length(max: 255, maxMessage: 'La description ne peut pas depasser {{ limit }} caracteres')]
        public string $description,
        #[Assert\PositiveOrZero(message: 'Le prix ne peut pas etre negatif')]
        public float $price,
        #[Assert\NotBlank(message: 'La localisation est requise')]
        #[Assert\Length(max: 255, maxMessage: 'La localisation ne peut pas depasser {{ limit }} caracteres')]
        public string $localisationDetails,
        #[Assert\Positive(message: 'La categorie est requise')]
        public int $categoryId,
        #[Assert\Positive(message: 'La ville est requise')]
        public int $cityId,
        public ListingStatus $status,
    ) {
    }
}
