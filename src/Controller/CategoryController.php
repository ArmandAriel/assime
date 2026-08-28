<?php

namespace App\Controller;

use App\Domain\Api\ApiValidator;
use App\Domain\Interfaces\CrudCategoryInterface;
use App\Dto\Request\Category\CreateCategoryRequestDto;
use App\Dto\Request\Category\UpdateCategoryRequestDto;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class CategoryController extends AbstractController
{
    public function __construct(
        private readonly CrudCategoryInterface $crudCategory,
        private readonly ApiValidator $apiValidator,
    ) {
    }

    #[OA\Get(
        path: '/api/category/get',
        operationId: 'getCategory',
        summary: 'Retourne la catégorie de démonstration',
        tags: ['Category']
    )]
    #[OA\Response(
        response: 200,
        description: 'Catégorie récupérée avec succès',
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
    #[Route('/api/category/get', name: 'app_category_get', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $result = $this->crudCategory->Get();
        return $this->json($result->toArray(), 200, [], ['groups' => ['category']]);
    }

    #[OA\Post(
        path: '/api/category/create',
        operationId: 'CreateCategory',
        summary: 'Add new category',
        tags: ['Category']
    )]
    #[OA\Response(
        response: 200,
        description: 'Add new category',
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
    #[Route('/api/category/create', name: 'app_category_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        $dto = new CreateCategoryRequestDto(
            name: (string) ($payload['name'] ?? ''),
            description: (string) ($payload['description'] ?? ''),
            idParent: isset($payload['idParent']) ? (int) $payload['idParent'] : null,
        );

        if ($validationError = $this->apiValidator->validate($dto)) {
            return $this->json($validationError->toArray(), $validationError->code);
        }

        $result = $this->crudCategory->Add($dto);

        return $this->json($result->toArray(), $result->code);
    }

    #[OA\Put(
        path: '/api/category/update',
        operationId: 'UpdateCategory',
        summary: 'Update category',
        tags: ['Category']
    )]
    #[OA\Response(
        response: 200,
        description: 'Update category',
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
    #[Route('/api/category/update', name: 'app_category_update', methods: ['PUT'])]
    public function update(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        $dto = new UpdateCategoryRequestDto(
            id: (int) ($payload['id'] ?? 0),
            name: (string) ($payload['name'] ?? ''),
            description: (string) ($payload['description'] ?? ''),
            idParent: isset($payload['idParent']) ? (int) $payload['idParent'] : null,
        );

        if ($validationError = $this->apiValidator->validate($dto)) {
            return $this->json($validationError->toArray(), $validationError->code);
        }

        $result = $this->crudCategory->Update($dto);

        return $this->json($result->toArray(), $result->code);
    }

    #[OA\Get(
        path: '/api/category/get/{id}',
        operationId: 'getCategoryById',
        summary: 'Retourne la catégorie',
        tags: ['Category']
    )]
    #[OA\Response(
        response: 200,
        description: 'Catégorie récupérée avec succès',
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
    #[Route('/api/category/get/{id}', name: 'app_category_byid', methods: ['GET'])]
    public function get(int $id): JsonResponse
    {
        $result = $this->crudCategory->GetById($id);
        return $this->json($result->toArray(), 200, [], ['groups' => ['category']]);
    }
}
