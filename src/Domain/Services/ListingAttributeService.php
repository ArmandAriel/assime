<?php

namespace App\Domain\Services;

use App\Domain\Api\ApiResponse;
use App\Domain\Interfaces\ListingAttributeInterface;
use App\Dto\Request\Listing\SetListingAttributesRequestDto;
use App\Entity\CategoryAttribute;
use App\Entity\ListingAttributeValue;
use App\Entity\User;
use App\Enums\Code;
use App\Repository\CategoryAttributeRepository;
use App\Repository\ListingAttributeValueRepository;
use App\Repository\ListingRepository;
use Doctrine\ORM\EntityManagerInterface;

class ListingAttributeService implements ListingAttributeInterface
{
    public function __construct(
        private readonly ListingRepository $listingRepository,
        private readonly CategoryAttributeRepository $categoryAttributeRepository,
        private readonly ListingAttributeValueRepository $listingAttributeValueRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function SetValues(int $listingId, SetListingAttributesRequestDto $request, User $currentUser): ApiResponse
    {
        $listing = $this->listingRepository->find($listingId);
        if (null === $listing) {
            return ApiResponse::error(
                message: 'Annonce introuvable',
                code: Code::NOT_FOUND->value,
            );
        }

        if ($listing->getOwner()->getId() !== $currentUser->getId()) {
            return ApiResponse::error(
                message: "Vous n'etes pas autorise a modifier cette annonce",
                code: Code::FORBIDDEN->value,
            );
        }

        /** @var CategoryAttribute[] $categoryAttributes */
        $categoryAttributes = $this->categoryAttributeRepository->findBy(['category' => $listing->getCategory()]);
        $categoryAttributesById = [];
        foreach ($categoryAttributes as $attribute) {
            $categoryAttributesById[$attribute->getId()] = $attribute;
        }

        $submitted = [];
        foreach ($request->values as $item) {
            $submitted[$item->categoryAttributeId] = $item->value;

            if (!isset($categoryAttributesById[$item->categoryAttributeId])) {
                return ApiResponse::error(
                    message: "Un des attributs envoyes n'appartient pas a la categorie de cette annonce",
                    code: Code::NOT_VALID->value,
                );
            }
        }

        $errors = [];
        $resolved = [];

        foreach ($categoryAttributes as $attribute) {
            $raw = $submitted[$attribute->getId()] ?? null;
            $raw = is_string($raw) ? trim($raw) : $raw;

            if (null === $raw || '' === $raw) {
                if ($attribute->isRequired()) {
                    $errors[$attribute->getCode()] = [sprintf('%s est requis', $attribute->getLabel())];
                }
                continue;
            }

            $coerced = $this->coerceValue($attribute, $raw);
            if (null === $coerced) {
                $errors[$attribute->getCode()] = [sprintf('Valeur invalide pour %s', $attribute->getLabel())];
                continue;
            }

            $resolved[] = [$attribute, $coerced];
        }

        if ([] !== $errors) {
            return ApiResponse::error(
                message: 'Donnees invalides',
                code: Code::NOT_VALID->value,
                errors: $errors,
            );
        }

        foreach ($this->listingAttributeValueRepository->findBy(['listing' => $listing]) as $existing) {
            $this->entityManager->remove($existing);
        }

        foreach ($resolved as [$attribute, $coerced]) {
            [$kind, $value] = $coerced;

            $listingAttributeValue = new ListingAttributeValue();
            $listingAttributeValue->setListing($listing);
            $listingAttributeValue->setCategoryAttribute($attribute);

            match ($kind) {
                'number' => $listingAttributeValue->setValueNumber($value),
                'boolean' => $listingAttributeValue->setValueBoolean($value),
                'date' => $listingAttributeValue->setValueDate($value),
                default => $listingAttributeValue->setValueText($value),
            };

            $this->entityManager->persist($listingAttributeValue);
        }

        $this->entityManager->flush();

        return $this->GetValues($listingId);
    }

    public function GetValues(int $listingId): ApiResponse
    {
        $listing = $this->listingRepository->find($listingId);
        if (null === $listing) {
            return ApiResponse::error(
                message: 'Annonce introuvable',
                code: Code::NOT_FOUND->value,
            );
        }

        $values = $this->listingAttributeValueRepository->findBy(['listing' => $listing]);

        return ApiResponse::success(
            message: 'Attributs recuperes avec succes',
            code: Code::SUCCESS->value,
            data: array_map($this->mapValue(...), $values)
        );
    }

    /**
     * @return array{0: string, 1: mixed}|null a [kind, coercedValue] pair, or null if the raw input is invalid for the attribute's type
     */
    private function coerceValue(CategoryAttribute $attribute, string $raw): ?array
    {
        return match ($attribute->getType()) {
            'text' => ['text', $raw],
            'number' => is_numeric($raw) ? ['number', (float) $raw] : null,
            'boolean' => match (strtolower($raw)) {
                '1', 'true', 'oui', 'yes' => ['boolean', true],
                '0', 'false', 'non', 'no' => ['boolean', false],
                default => null,
            },
            'date' => $this->parseDate($raw),
            'select' => $this->resolveSelectValue($attribute, $raw),
            default => null,
        };
    }

    /**
     * @return array{0: string, 1: \DateTime}|null
     */
    private function parseDate(string $raw): ?array
    {
        try {
            return ['date', new \DateTime($raw)];
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * @return array{0: string, 1: string}|null
     */
    private function resolveSelectValue(CategoryAttribute $attribute, string $raw): ?array
    {
        foreach ($attribute->getAttributeOptions() as $option) {
            if ($option->getValue() === $raw) {
                return ['text', $raw];
            }
        }

        return null;
    }

    /**
     * @return array{categoryAttributeId: int|null, code: string|null, label: string|null, type: string|null, value: mixed}
     */
    private function mapValue(ListingAttributeValue $listingAttributeValue): array
    {
        $attribute = $listingAttributeValue->getCategoryAttribute();

        $value = match ($attribute?->getType()) {
            'number' => $listingAttributeValue->getValueNumber(),
            'boolean' => $listingAttributeValue->isValueBoolean(),
            'date' => $listingAttributeValue->getValueDate()?->format('Y-m-d'),
            default => $listingAttributeValue->getValueText(),
        };

        return [
            'categoryAttributeId' => $attribute?->getId(),
            'code' => $attribute?->getCode(),
            'label' => $attribute?->getLabel(),
            'type' => $attribute?->getType(),
            'value' => $value,
        ];
    }
}
