<?php

namespace App\Domain\Services;

use App\Domain\Api\ApiResponse;
use App\Domain\Interfaces\CrudAttributeOptionInterface;
use App\Dto\Request\AttributeOption\CreateAttributeOptionRequestDto;
use App\Dto\Request\AttributeOption\UpdateAttributeOptionRequestDto;
use App\Entity\AttributeOption;
use App\Enums\Code;
use App\Repository\AttributeOptionRepository;
use App\Repository\CategoryAttributeRepository;
use Doctrine\ORM\EntityManagerInterface;

class AttributeOptionService implements CrudAttributeOptionInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CategoryAttributeRepository $categoryAttributeRepository,
        private readonly AttributeOptionRepository $attributeOptionRepository
    ) {
    }

    public function add(CreateAttributeOptionRequestDto $request): ApiResponse
    {
        $categoryAttribute = $this->categoryAttributeRepository->find($request->categoryAttributeId);
        if (!$categoryAttribute) {
            return ApiResponse::error(
                message: 'Category attribute not found.',
                code: Code::NOT_FOUND->value,
            );
        }

        $attributeOption = new AttributeOption();
        $attributeOption->setValue($request->value);
        $attributeOption->setLabel($request->label);
        $attributeOption->setCategoryAttribute($categoryAttribute);

        $this->entityManager->persist($attributeOption);
        $this->entityManager->flush();

        return ApiResponse::success(
            message: 'Attribute option created successfully.',
            code: Code::CREATED->value,
            data: $this->mapOption($attributeOption)
        );
    }

    public function update(UpdateAttributeOptionRequestDto $request): ApiResponse
    {
        $attributeOption = $this->attributeOptionRepository->find($request->id);
        if (!$attributeOption) {
            return ApiResponse::error(
                message: 'Attribute option not found.',
                code: Code::NOT_FOUND->value,
            );
        }

        $attributeOption->setValue($request->value);
        $attributeOption->setLabel($request->label);

        $this->entityManager->flush();

        return ApiResponse::success(
            message: 'Attribute option updated successfully.',
            code: Code::UPDATED->value,
            data: $this->mapOption($attributeOption)
        );
    }

    public function get(?int $categoryAttributeId = null): ApiResponse
    {
        $criteria = null !== $categoryAttributeId ? ['categoryAttribute' => $categoryAttributeId] : [];
        $options = $this->attributeOptionRepository->findBy($criteria);

        return ApiResponse::success(
            message: 'Attribute options retrieved successfully.',
            code: Code::SUCCESS->value,
            data: array_map($this->mapOption(...), $options)
        );
    }

    public function getById(int $id): ApiResponse
    {
        $attributeOption = $this->attributeOptionRepository->find($id);
        if (!$attributeOption) {
            return ApiResponse::error(
                message: 'Attribute option not found.',
                code: Code::NOT_FOUND->value,
            );
        }

        return ApiResponse::success(
            message: 'Attribute option retrieved successfully.',
            code: Code::SUCCESS->value,
            data: $this->mapOption($attributeOption)
        );
    }

    /**
     * @return array{id: int|null, value: string|null, label: string|null, categoryAttributeId: int|null}
     */
    private function mapOption(AttributeOption $option): array
    {
        return [
            'id' => $option->getId(),
            'value' => $option->getValue(),
            'label' => $option->getLabel(),
            'categoryAttributeId' => $option->getCategoryAttribute()?->getId(),
        ];
    }
}
