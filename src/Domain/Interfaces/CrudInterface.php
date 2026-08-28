<?php

namespace App\Domain\Interfaces;

use App\Domain\Api\ApiResponse;
use App\Dto\Request\Common\CreateCommonRequestDto;
use App\Dto\Request\Common\UpdateCommonRequestDto;

interface CrudInterface
{
    public function Add(CreateCommonRequestDto $request): ApiResponse;
    public function Update(UpdateCommonRequestDto $request): ApiResponse;
    public function Delete(int $id): ApiResponse;
    public function Get(): ApiResponse;
}
