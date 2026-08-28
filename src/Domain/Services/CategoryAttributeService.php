<?php

namespace App\Domain\Services;
use App\Domain\Api\ApiResponse;
use App\Domain\Interfaces\CrudCategoryAttributeInterface;
use App\Dto\Request\CategoryAttribute\CreateCategoryAttributeRequestDto;
use App\Dto\Request\CategoryAttribute\UpdateCategoryAttributeRequestDto;
use App\Enums\Code;
use App\Repository\CategoryAttributeRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;

class CategoryAttributeService implements CrudCategoryAttributeInterface
{
    public function __construct(
        private readonly CategoryAttributeRepository $categoryAttributeRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly EntityManagerInterface $entityManager, 
    )
    {
    }

    public function Add(CreateCategoryAttributeRequestDto $request): ApiResponse
    {
        if($request->code === null || $request->label === null || $request->type === null) {
            return ApiResponse::error(
                'Code, label and type are required fields.',
                code: Code::NOT_VALID->value
            );
        }

        if ($this->categoryAttributeRepository->findOneBy(['code' => $request->code])) {
            return ApiResponse::error(
                'Category attribute with the same code already exists.',
                code: Code::CONFLICT->value
            );
        }



       $category = $this->categoryRepository->find($request->idCategory);
        if (!$category) {
            return ApiResponse::error(
                'Category not found.',
                code: Code::NOT_FOUND->value
            );
        }

       $attribueToCreate = new \App\Entity\CategoryAttribute(
            $request->code,
            $request->label,
            $request->type,
            $request->isRequired,
            $category
        );

        $this->entityManager->persist($attribueToCreate);
        $this->entityManager->flush();

       return ApiResponse::success(
            'Category attribute created successfully.',
            code: Code::CREATED->value,
            data: [
                'id' => $attribueToCreate->getId(),
                'code' => $attribueToCreate->getCode(),
                'label' => $attribueToCreate->getLabel(),
                'type' => $attribueToCreate->getType(),
                'isRequired' => $attribueToCreate->isRequired(),
            ]
        );
      

    }

    public function Update(UpdateCategoryAttributeRequestDto $request): ApiResponse
    {
        $attribute = $this->categoryAttributeRepository->find($request->id);
        if (null === $attribute) {
            return ApiResponse::error(
                'Attribute not found.',
                code: Code::NOT_FOUND->value
            );
        }

        $existingWithCode = $this->categoryAttributeRepository->findOneBy(['code' => $request->code]);
        if (null !== $existingWithCode && $existingWithCode->getId() !== $attribute->getId()) {
            return ApiResponse::error(
                'Category attribute with the same code already exists.',
                code: Code::CONFLICT->value
            );
        }

        $attribute
            ->setCode($request->code)
            ->setLabel($request->label)
            ->setType($request->type)
            ->setIsRequired($request->isRequired);

        $this->entityManager->flush();

        return ApiResponse::success(
            'Category attribute updated successfully.',
            code: Code::UPDATED->value,
            data: [
                'id' => $attribute->getId(),
                'code' => $attribute->getCode(),
                'label' => $attribute->getLabel(),
                'type' => $attribute->getType(),
                'isRequired' => $attribute->isRequired(),
            ]
        );
    }

    public function Get(): ApiResponse
    {
        $attributes = $this->categoryAttributeRepository->findAll();
        if(null === $attributes){
            return  ApiResponse::error(
                'No attributes found.',
                code: Code::NOT_FOUND->value
            );
        }

        $data = array_map(function($attributes){
            return [
                'id' => $attributes->getId(),
                'code' => $attributes->getCode(),
                'label' => $attributes->getLabel(),
                'type' => $attributes->getType(),
                'isRequired' => $attributes->isRequired(),
            ];
        }, $attributes);

         return ApiResponse::success(
            'Category attribute retrieved successfully.',
            code: Code::SUCCESS->value,
            data: $data
        );
    }

    public function GetById(int $id): ApiResponse
    {
        $attribute = $this->categoryAttributeRepository->find($id);
        if(null === $attribute){
            return  ApiResponse::error(
                'Attribute not found.',
                code: Code::NOT_FOUND->value
            );
        }


        return ApiResponse::success(
            'Category attribute retrieved successfully.',
            code: Code::SUCCESS->value,
            data: [
                'id' => $attribute->getId(),
                'code' => $attribute->getCode(),
                'label' => $attribute->getLabel(),
                'type' => $attribute->getType(),
                'isRequired' => $attribute->isRequired(),
            ]
        );
    }

    public function getByCategory(int $id): ApiResponse
    {
        $category = $this->categoryRepository->find($id);
        if (!$category) {
            return ApiResponse::error(
                'Category not found.',
                code: Code::NOT_FOUND->value
            );
        }

        $attributes = $this->categoryAttributeRepository->findBy(['category' => $category]);
        if (null === $attributes) {
            return ApiResponse::error(
                'No attributes found for this category.',
                code: Code::NOT_FOUND->value
            );
        }

        $data = array_map(function ($attribute) {
            return [
                'id' => $attribute->getId(),
                'code' => $attribute->getCode(),
                'label' => $attribute->getLabel(),
                'type' => $attribute->getType(),
                'isRequired' => $attribute->isRequired(),
            ];
        }, $attributes);

        return ApiResponse::success(
            'Category attributes retrieved successfully.',
            code: Code::SUCCESS->value,
            data: $data
        );
    }
}
