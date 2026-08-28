<?php

namespace App\Dto\Request\Listing;

use App\Enums\ListingStatus;

final readonly class ListingSearchRequestDto
{
    public function __construct(
        public ?int $categoryId = null,
        public ?int $cityId = null,
        public ?int $ownerId = null,
        public ?ListingStatus $status = null,
        public ?float $minPrice = null,
        public ?float $maxPrice = null,
        public ?string $q = null,
        public int $page = 1,
        public int $limit = 20,
    ) {
    }
}
