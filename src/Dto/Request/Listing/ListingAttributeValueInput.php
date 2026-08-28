<?php

namespace App\Dto\Request\Listing;

final readonly class ListingAttributeValueInput
{
    public function __construct(
        public int $categoryAttributeId,
        public ?string $value,
    ) {
    }
}
