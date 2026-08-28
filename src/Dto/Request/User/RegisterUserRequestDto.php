<?php

namespace App\Dto\Request\User;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class RegisterUserRequestDto
{
    public function __construct(
        #[Assert\NotBlank(message: "L'email est requis")]
        #[Assert\Email(message: "L'email n'est pas valide")]
        public string $email,
        #[Assert\NotBlank(message: 'Le mot de passe est requis')]
        #[Assert\Length(min: 8, minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caracteres')]
        public string $password,
        #[Assert\Length(max: 150, maxMessage: 'Le nom ne peut pas depasser {{ limit }} caracteres')]
        public ?string $displayName = null,
        #[Assert\Length(max: 30, maxMessage: 'Le telephone ne peut pas depasser {{ limit }} caracteres')]
        public ?string $phone = null,
    ) {
    }
}
