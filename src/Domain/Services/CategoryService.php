<?php

namespace App\Domain\Services;

use App\Domain\Api\ApiResponse;
use App\Domain\Interfaces\CrudCategoryInterface;
use App\Dto\Request;
use App\Entity\Category;
use App\Enums\Code;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;

class CategoryService implements CrudCategoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CategoryRepository $categoryRepository,
    ) {
    }

    public function Add(Request\Category\CreateCategoryRequestDto $request): ApiResponse
    {
        $category = new Category($request->name, true);
        $category->setDescription($request->description);
        $category->setIdParent($request->idParent);

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return ApiResponse::success(
            'Category created successfully.',
            Code::CREATED->value,
            [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'description' => $category->getDescription(),
                'idParent' => $category->getIdParent(),
            ]
        );
    }

    public function Get(): ApiResponse
    {
        $list = $this->categoryRepository->findBy([
            'Active' => true,
        ]);

        $data = array_map(
            fn ($categoery) => [
                'id' => $categoery->getId(),
                'name' => $categoery->getName(),
                'description' => $categoery->getDescription(),
                'idParent' => $categoery->getIdParent(),
                'active' => $categoery->isActive(),
            ],
            $list
        );

        return ApiResponse::success(
            message: 'Categories retrieved successfully.',
            code: Code::SUCCESS->value,
            data: $data
        );
    }

    public function Update(Request\Category\UpdateCategoryRequestDto $request): ApiResponse
    {
        $existing = $this->categoryRepository->find($request->id);
        if (!$existing) {
            return ApiResponse::error('Category not found.', code: Code::NOT_FOUND->value);
        }

        $existing->setName($request->name);
        $existing->setDescription($request->description);
        $existing->setIdParent($request->idParent);

        $this->entityManager->flush();

        return ApiResponse::success(
            'Category updated successfully.',
            Code::UPDATED->value,
            [
                'id' => $existing->getId(),
                'name' => $existing->getName(),
                'description' => $existing->getDescription(),
                'idParent' => $existing->getIdParent(),
                'active' => $existing->isActive(),
            ]
        );
    }

    public function GetById(int $id): ApiResponse
    {
        $existing = $this->categoryRepository->find($id);
        if (!$existing) {
            return ApiResponse::error('Category not found.', code: Code::NOT_FOUND->value);
        }

        $data = [
            'id' => $existing->getId(),
            'name' => $existing->getName(),
            'description' => $existing->getDescription(),
            'idParent' => $existing->getIdParent(),
            'active' => $existing->isActive(),
        ];

        return ApiResponse::success(
            message: 'Category retrieved successfully.',
            code: Code::SUCCESS->value,
            data: $data
        );
    }
}
