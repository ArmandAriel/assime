<?php

namespace App\Dto\Request\Category;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateCategoryRequestDto
{
    public function __construct(
        #[Assert\Positive(message: "L'identifiant de la categorie est requis")]
        public int $id,
        #[Assert\NotBlank(message: 'Le nom est requis')]
        #[Assert\Length(max: 100, maxMessage: 'Le nom ne peut pas depasser {{ limit }} caracteres')]
        public string $name,
        #[Assert\NotBlank(message: 'La description est requise')]
        #[Assert\Length(max: 255, maxMessage: 'La description ne peut pas depasser {{ limit }} caracteres')]
        public string $description,
        public ?int $idParent,
    ) {
    }
}
