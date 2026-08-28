<?php

namespace App\Dto\Request\AttributeOption;

use Symfony\Component\Validator\Constraints as Assert;

class CreateAttributeOptionRequestDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'La valeur est requise')]
        #[Assert\Length(max: 100)]
        public string $value,
        #[Assert\NotBlank(message: 'Le libelle est requis')]
        #[Assert\Length(max: 100)]
        public string $label,
        #[Assert\Positive(message: "L'attribut est requis")]
        public int $categoryAttributeId
    ) {
    }
}
