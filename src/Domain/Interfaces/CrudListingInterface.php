<?php

namespace App\Domain\Interfaces;

use App\Domain\Api\ApiResponse;
use App\Dto\Request\Listing\CreateListingRequestDto;
use App\Dto\Request\Listing\ListingSearchRequestDto;
use App\Dto\Request\Listing\UpdateListingRequestDto;
use App\Entity\User;

interface CrudListingInterface
{
    public function Add(CreateListingRequestDto $request, User $owner): ApiResponse;
    public function Update(UpdateListingRequestDto $request, User $currentUser): ApiResponse;
    public function Get(ListingSearchRequestDto $filters): ApiResponse;
    public function GetById(int $id): ApiResponse;
    public function Delete(int $id, User $currentUser): ApiResponse;
}
