<?php

namespace App\Domain\Api;

final readonly class ApiResponse
{
    public function __construct(
        public bool $success,
        public string $message = '',
        public int $code = 0,
        public array $data = [],
        public array $errors = []
    ) {
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'code' => $this->code,
            'data' => $this->data,
            'errors' => $this->errors,
            ];
    }

    public static function success(string $message, int $code, array $data = [], array $errors = []): self
    {
        return new self(true, $message, $code, $data, $errors);
    }

    public static function error(string $message, int $code, array $data = [], array $errors = []): self
    {
        return new self(false, $message, $code, $data, $errors);
    }
}
