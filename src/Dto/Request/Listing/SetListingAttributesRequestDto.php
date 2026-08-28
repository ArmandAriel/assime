<?php

namespace App\Dto\Request\Listing;

final readonly class SetListingAttributesRequestDto
{
    /**
     * @param list<ListingAttributeValueInput> $values
     */
    public function __construct(
        public array $values,
    ) {
    }
}
