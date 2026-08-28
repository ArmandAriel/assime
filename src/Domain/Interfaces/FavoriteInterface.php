<?php

namespace App\Domain\Interfaces;

use App\Domain\Api\ApiResponse;
use App\Entity\User;

interface FavoriteInterface
{
    public function Add(int $listingId, User $user): ApiResponse;
    public function Remove(int $listingId, User $user): ApiResponse;
    public function GetMine(User $user): ApiResponse;
}
