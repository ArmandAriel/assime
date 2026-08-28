<?php

namespace App\Domain\Interfaces;

use App\Domain\Api\ApiResponse;
use App\Dto\Request\City\CreateCityRequestDto;
use App\Dto\Request\City\UpdateCityRequestDto;

interface CrudCityInterface
{
    public function add(CreateCityRequestDto $request): ApiResponse;
    public function update(UpdateCityRequestDto $request): ApiResponse;
    public function get(): ApiResponse;
    public function getByDepartment(int $departmentId): ApiResponse;
}
