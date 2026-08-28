<?php

namespace App\Domain\Interfaces;

use App\Domain\Api\ApiResponse;
use App\Dto\Request\DepartmentDto\CreateDepartmentRequestDto;
use App\Dto\Request\DepartmentDto\UpdateDepartmentRequestDto;

interface CrudDepartmentInterface
{
    public function add(CreateDepartmentRequestDto $request): ApiResponse;
    public function update(UpdateDepartmentRequestDto $request): ApiResponse;
    public function get(): ApiResponse;
}
