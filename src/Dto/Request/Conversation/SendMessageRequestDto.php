<?php

namespace App\Dto\Request\Conversation;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class SendMessageRequestDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'Le message ne peut pas etre vide', normalizer: 'trim')]
        #[Assert\Length(max: 2000, maxMessage: 'Le message ne peut pas depasser {{ limit }} caracteres')]
        public string $content,
    ) {
    }
}
