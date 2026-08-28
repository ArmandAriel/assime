<?php

namespace App\Domain\Interfaces;

use App\Domain\Api\ApiResponse;
use App\Dto\Request\Listing\SetListingAttributesRequestDto;
use App\Entity\User;

interface ListingAttributeInterface
{
    public function SetValues(int $listingId, SetListingAttributesRequestDto $request, User $currentUser): ApiResponse;
    public function GetValues(int $listingId): ApiResponse;
}
