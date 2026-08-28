<?php

namespace App\Domain\Interfaces;

use App\Domain\Api\ApiResponse;
use App\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface ListingImageInterface
{
    public function UploadImage(int $listingId, UploadedFile $file, User $currentUser): ApiResponse;
    public function DeleteImage(int $listingId, int $imageId, User $currentUser): ApiResponse;
}
