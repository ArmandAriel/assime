<?php

namespace App\Dto\Request\Category;

class ByIdCategoryRequest
{
    public function __construct(
        public int $id
    ) {
    }
}
