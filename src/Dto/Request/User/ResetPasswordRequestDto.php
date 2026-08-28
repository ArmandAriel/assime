<?php

namespace App\Dto\Request\User;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class ResetPasswordRequestDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'Le lien de reinitialisation est invalide')]
        public string $token,
        #[Assert\NotBlank(message: 'Le mot de passe est requis')]
        #[Assert\Length(min: 8, minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caracteres')]
        public string $newPassword,
    ) {
    }
}
