<?php

namespace App\Dto\Request\City;

use App\Dto\Request\Common\CreateCommonRequestDto;

class CreateCityRequestDto extends CreateCommonRequestDto
{
    public int $departmentId;
}
