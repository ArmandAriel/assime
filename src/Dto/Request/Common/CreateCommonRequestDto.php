<?php

namespace App\Dto\Request\Common;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

class CreateCommonRequestDto
{
    //    public function __construct(
    //        public string $name,
    //      ) {
    //    }

    #[OA\Property(description: "L'adresse email de l'utilisateur", example: 'user@example.com')]
    #[Assert\NotBlank]
    public string $name;
}
