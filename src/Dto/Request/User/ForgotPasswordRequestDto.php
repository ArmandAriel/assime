<?php

namespace App\Dto\Request\User;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class ForgotPasswordRequestDto
{
    public function __construct(
        #[Assert\NotBlank(message: "L'email est requis")]
        #[Assert\Email(message: "L'email n'est pas valide")]
        public string $email,
    ) {
    }
}
