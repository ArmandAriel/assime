<?php

namespace App\Controller;

use App\Domain\Api\ApiValidator;
use App\Domain\Interfaces\CrudAttributeOptionInterface;
use App\Dto\Request\AttributeOption\CreateAttributeOptionRequestDto;
use App\Dto\Request\AttributeOption\UpdateAttributeOptionRequestDto;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class AttributeOptionController extends AbstractController
{
     public function __construct(
        private readonly CrudAttributeOptionInterface $categoryAttributeService,
        private readonly ApiValidator $apiValidator,
    )
    {
    }

    #[Route('/attribute/option', name: 'app_attribute_option')]
    public function index(): Response
    {
        return $this->render('attribute_option/index.html.twig', [
            'controller_name' => 'AttributeOptionController',
        ]);
    }


      #[OA\Post(
        path: '/api/category/attribute/option/create',
        operationId: 'CreateCategoryAttributeOption',
        summary: 'Add new category attribute option',
        tags: ['CategoryAttributeOption']
    )]
    #[OA\Response(
        response: 200,
        description: 'Add new category attribute option',
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
    #[Route('/api/category/attribute/option/create', name: 'app_category_attribute_option_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] CreateAttributeOptionRequestDto $request): JsonResponse
    {
        if ($validationError = $this->apiValidator->validate($request)) {
            return $this->json($validationError->toArray(), $validationError->code);
        }

        $result = $this->categoryAttributeService->add($request);
        return new JsonResponse($result->toArray(), $result->code);
    }

    #[OA\Put(
        path: '/api/category/attribute/option/update',
        operationId: 'UpdateCategoryAttributeOption',
        summary: 'Update a category attribute option',
        tags: ['CategoryAttributeOption']
    )]
    #[OA\Response(response: 200, description: 'Update category attribute option')]
    #[Route('/api/category/attribute/option/update', name: 'app_category_attribute_option_update', methods: ['PUT'])]
    public function update(#[MapRequestPayload] UpdateAttributeOptionRequestDto $request): JsonResponse
    {
        if ($validationError = $this->apiValidator->validate($request)) {
            return $this->json($validationError->toArray(), $validationError->code);
        }

        $result = $this->categoryAttributeService->update($request);
        return new JsonResponse($result->toArray(), $result->code);
    }

    #[OA\Get(
        path: '/api/category/attribute/option/get',
        operationId: 'GetCategoryAttributeOptions',
        summary: 'List attribute options, optionally filtered by categoryAttributeId',
        tags: ['CategoryAttributeOption']
    )]
    #[OA\Parameter(name: 'categoryAttributeId', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Attribute options retrieved')]
    #[Route('/api/category/attribute/option/get', name: 'app_category_attribute_option_get', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $categoryAttributeId = $request->query->has('categoryAttributeId')
            ? (int) $request->query->get('categoryAttributeId')
            : null;

        $result = $this->categoryAttributeService->get($categoryAttributeId);
        return new JsonResponse($result->toArray(), $result->code);
    }

    #[OA\Get(
        path: '/api/category/attribute/option/get/{id}',
        operationId: 'GetCategoryAttributeOptionById',
        summary: 'Get an attribute option by ID',
        tags: ['CategoryAttributeOption']
    )]
    #[OA\Response(response: 200, description: 'Attribute option retrieved')]
    #[Route('/api/category/attribute/option/get/{id}', name: 'app_category_attribute_option_get_by_id', methods: ['GET'])]
    public function get(int $id): JsonResponse
    {
        $result = $this->categoryAttributeService->getById($id);
        return new JsonResponse($result->toArray(), $result->code);
    }
}
