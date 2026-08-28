<?php

namespace App\Dto\Request\AttributeOption;

use Symfony\Component\Validator\Constraints as Assert;

final class UpdateAttributeOptionRequestDto
{
    public function __construct(
        #[Assert\Positive(message: "L'identifiant de l'option est requis")]
        public int $id,
        #[Assert\NotBlank(message: 'La valeur est requise')]
        #[Assert\Length(max: 100)]
        public string $value,
        #[Assert\NotBlank(message: 'Le libelle est requis')]
        #[Assert\Length(max: 100)]
        public string $label,
    ) {
    }
}
