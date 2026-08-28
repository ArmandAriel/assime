<?php

namespace App\Domain\Interfaces;

use App\Domain\Api\ApiResponse;
use App\Dto\Request;

interface CrudCategoryInterface
{
    public function Add(Request\Category\CreateCategoryRequestDto $request): ApiResponse;
    public function Update(Request\Category\UpdateCategoryRequestDto $request): ApiResponse;
    public function Get(): ApiResponse;
    public function GetById(int $id): ApiResponse;
}
