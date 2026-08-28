<?php

namespace App\Dto\Request\City;

use App\Dto\Request\Common\UpdateCommonRequestDto;

class UpdateCityRequestDto extends UpdateCommonRequestDto
{
    public int $departmentId;
}
