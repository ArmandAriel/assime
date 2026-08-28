<?php

namespace App\Dto\Request\DepartmentDto;

use App\Dto\Request\Common\CreateCommonRequestDto;

class CreateDepartmentRequestDto extends CreateCommonRequestDto
{
    public int $regionId;
}
