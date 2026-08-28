<?php

namespace App\Domain\Interfaces;

use App\Domain\Api\ApiResponse;
use App\Dto\Request;
use App\Dto\Request\CategoryAttribute\CreateCategoryAttributeRequestDto;
use App\Dto\Request\CategoryAttribute\UpdateCategoryAttributeRequestDto;


interface CrudCategoryAttributeInterface
{
    public function Add(CreateCategoryAttributeRequestDto $request): ApiResponse;
    public function Update(UpdateCategoryAttributeRequestDto $request): ApiResponse;
    public function Get(): ApiResponse;
    public function GetById(int $id): ApiResponse;
    public function getByCategory(int $id): ApiResponse;
}
