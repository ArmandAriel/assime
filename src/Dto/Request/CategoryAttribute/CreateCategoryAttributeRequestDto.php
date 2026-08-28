<?php

namespace App\Dto\Request\CategoryAttribute;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateCategoryAttributeRequestDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'Le code est requis')]
        #[Assert\Length(max: 100)]
        public string $code,
        #[Assert\NotBlank(message: "Le libelle est requis")]
        #[Assert\Length(max: 100)]
        public string $label,
        #[Assert\NotBlank(message: 'Le type est requis')]
        #[Assert\Choice(choices: ['text', 'number', 'boolean', 'select', 'date'], message: 'Type invalide (text, number, boolean, select ou date)')]
        public string $type,
        public bool $isRequired,
        #[Assert\Positive(message: 'La categorie est requise')]
        public int $idCategory
    ) {
    }
}
