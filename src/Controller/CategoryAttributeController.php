<?php

namespace App\Controller;

use App\Domain\Api\ApiValidator;
use App\Domain\Interfaces\CrudCategoryAttributeInterface;
use App\Dto\Request\CategoryAttribute\CreateCategoryAttributeRequestDto;
use App\Dto\Request\CategoryAttribute\UpdateCategoryAttributeRequestDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

final class CategoryAttributeController extends AbstractController
{

    public function __construct(
        private readonly CrudCategoryAttributeInterface $categoryAttributeService,
        private readonly ApiValidator $apiValidator,
    )
    {
    }


 #[OA\Get(
        path: '/api/category/attribute/get',
        operationId: 'GetCategoryAttribute',
        summary: 'Get category attributes',
        tags: ['CategoryAttribute']
    )]
    #[OA\Response(
        response: 200,
        description: 'Get category attributes',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: 'true'),
                new OA\Property(property: 'message', type: 'string', example: 'operation succeeded'),
                new OA\Property(property: 'code', type: 'int', example: '200/201/204'),
                new OA\Property(property: 'data', type: 'non-empty-array<string>', example: 'object returned'),
            ],
            type: 'object'
        )
    )]
    #[Route('/api/category/attribute/get', name: 'app_category_attribute_get', methods: ['GET'])]
    public function index(): Response
    {
        $result = $this->categoryAttributeService->Get();
        return new JsonResponse($result->toArray(), $result->code);
    }


    #[OA\Post(
        path: '/api/category/attribute/create',
        operationId: 'CreateCategoryAttribute',
        summary: 'Add new category attribute',
        tags: ['CategoryAttribute']
    )]
    #[OA\Response(
        response: 200,
        description: 'Add new category attribute',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: 'true'),
                new OA\Property(property: 'message', type: 'string', example: 'operation succeeded'),
                new OA\Property(property: 'code', type: 'int', example: '200/201/204'),
                new OA\Property(property: 'data', type: 'non-empty-array<string>', example: 'object returned'),
            ],
            type: 'object'
        )
    )]
    #[Route('/api/category/attribute/create', name: 'app_category_attribute_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] CreateCategoryAttributeRequestDto $request): JsonResponse
    {
        if ($validationError = $this->apiValidator->validate($request)) {
            return $this->json($validationError->toArray(), $validationError->code);
        }

        $result = $this->categoryAttributeService->Add($request);
        return new JsonResponse($result->toArray(), $result->code);
    }

    #[OA\Put(
        path: '/api/category/attribute/update',
        operationId: 'UpdateCategoryAttribute',
        summary: 'Update a category attribute',
        tags: ['CategoryAttribute']
    )]
    #[OA\Response(
        response: 200,
        description: 'Update category attribute',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: 'true'),
                new OA\Property(property: 'message', type: 'string', example: 'operation succeeded'),
                new OA\Property(property: 'code', type: 'int', example: '202'),
                new OA\Property(property: 'data', type: 'object'),
            ],
            type: 'object'
        )
    )]
    #[Route('/api/category/attribute/update', name: 'app_category_attribute_update', methods: ['PUT'])]
    public function update(#[MapRequestPayload] UpdateCategoryAttributeRequestDto $request): JsonResponse
    {
        if ($validationError = $this->apiValidator->validate($request)) {
            return $this->json($validationError->toArray(), $validationError->code);
        }

        $result = $this->categoryAttributeService->Update($request);
        return new JsonResponse($result->toArray(), $result->code);
    }


 #[OA\Get(
        path: '/api/category/attribute/get/{id}',
        operationId: 'GetCategoryAttributeById',
        summary: 'Get category attribute by ID',
        tags: ['CategoryAttribute']
    )]
    #[OA\Response(
        response: 200,
        description: 'Get category attribute by ID',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: 'true'),
                new OA\Property(property: 'message', type: 'string', example: 'operation succeeded'),
                new OA\Property(property: 'code', type: 'int', example: '200/201/204'),
                new OA\Property(property: 'data', type: 'non-empty-array<string>', example: 'object returned'),
            ],
            type: 'object'
        )
    )]
    #[Route('/api/category/attribute/get/{id}', name: 'app_category_attribute_get_by_id', methods: ['GET'])]
    public function get(int $id): Response
    {
        $result = $this->categoryAttributeService->GetById($id);
        return new JsonResponse($result->toArray(), $result->code);
    }


 #[OA\Get(
        path: '/api/category/attribute/getByCategory/{id}',
        operationId: 'GetCategoryAttributeByCategory',
        summary: 'Get category attribute by category ID',
        tags: ['CategoryAttribute']
    )]
    #[OA\Response(
        response: 200,
        description: 'Get category attribute by category ID',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: 'true'),
                new OA\Property(property: 'message', type: 'string', example: 'operation succeeded'),
                new OA\Property(property: 'code', type: 'int', example: '200/201/204'),
                new OA\Property(property: 'data', type: 'non-empty-array<string>', example: 'object returned'),
            ],
            type: 'object'
        )
    )]
    #[Route('/api/category/attribute/getByCategory/{id}', name: 'app_category_attribute_get_by_category', methods: ['GET'])]
    public function getByCategory(int $id): Response
    {
        $result = $this->categoryAttributeService->getByCategory($id);
        return new JsonResponse($result->toArray(), $result->code);
    }
}
