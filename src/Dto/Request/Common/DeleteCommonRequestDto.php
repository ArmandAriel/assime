<?php

namespace App\Dto\Request\Common;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

class DeleteCommonRequestDto
{
    #[OA\Property(description: "id de l'entité", example: '')]
    #[Assert\NotNull]
    public int $id;
}
