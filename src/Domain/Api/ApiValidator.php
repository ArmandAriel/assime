<?php

namespace App\Domain\Api;

use App\Enums\Code;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Validates a request DTO against its Assert\* constraints and, when
 * invalid, formats the violations into the standard ApiResponse envelope
 * (as opposed to Symfony's default MapRequestPayload violation format,
 * which would break the {success, message, code, data, errors} shape the
 * rest of this API returns).
 */
final readonly class ApiValidator
{
    public function __construct(
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * @return ApiResponse|null null when the DTO is valid, otherwise a ready-to-return error response
     */
    public function validate(object $dto): ?ApiResponse
    {
        $violations = $this->validator->validate($dto);

        if (0 === count($violations)) {
            return null;
        }

        $errors = [];
        foreach ($violations as $violation) {
            $property = $violation->getPropertyPath();
            $errors[$property][] = $violation->getMessage();
        }

        return ApiResponse::error(
            message: 'Donnees invalides',
            code: Code::NOT_VALID->value,
            errors: $errors,
        );
    }
}
