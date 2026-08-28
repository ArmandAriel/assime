<?php

namespace App\Dto\Request\Category;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateCategoryRequestDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'Le nom est requis')]
        #[Assert\Length(max: 100, maxMessage: 'Le nom ne peut pas depasser {{ limit }} caracteres')]
        public string $name,
        // Required: the "category" table column is NOT NULL even though this
        // was previously typed nullable, which used to surface as a raw 500
        // SQL error instead of a clean validation message.
        #[Assert\NotBlank(message: 'La description est requise')]
        #[Assert\Length(max: 255, maxMessage: 'La description ne peut pas depasser {{ limit }} caracteres')]
        public string $description,
        public ?int $idParent,
    ) {
    }
}
