<?php

namespace App\Domain\Interfaces;

use App\Domain\Api\ApiResponse;
use App\Dto\Request\AttributeOption\CreateAttributeOptionRequestDto;
use App\Dto\Request\AttributeOption\UpdateAttributeOptionRequestDto;

interface CrudAttributeOptionInterface
{
    public function add(CreateAttributeOptionRequestDto $request): ApiResponse;
    public function update(UpdateAttributeOptionRequestDto $request): ApiResponse;
    public function get(?int $categoryAttributeId = null): ApiResponse;
    public function getById(int $id): ApiResponse;
}
