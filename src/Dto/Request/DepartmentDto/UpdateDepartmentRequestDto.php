<?php

namespace App\Dto\Request\DepartmentDto;

use App\Dto\Request\Common\UpdateCommonRequestDto;

class UpdateDepartmentRequestDto extends UpdateCommonRequestDto
{
    public int $regionId;
}
