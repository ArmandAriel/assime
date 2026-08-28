<?php

namespace App\Controller;

use App\Domain\Interfaces\CrudRegionInterface;
use App\Dto\Request\Common\CreateCommonRequestDto;
use App\Dto\Request\Common\UpdateCommonRequestDto;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class RegionController extends AbstractController
{
    public function __construct(
        private CrudRegionInterface $crudRegion,
    ) {
    }



    #[OA\Post(
        path: '/api/region/create',
        operationId: 'CreateRegion',
        summary: 'Add new region',
        tags: ['Region']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'name', type: 'string'),
              ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Add new region',
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
    #[Route('/api/region/create', name: 'app_region_create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] CreateCommonRequestDto $request
    ): Response {
        $result = $this->crudRegion->add($request);
        return new JsonResponse($result);
    }



    #[OA\Get(
        path: '/api/region/get',
        operationId: 'GetRegion',
        summary: 'Get active regions',
        tags: ['Region']
    )]
    #[OA\Response(
        response: 200,
        description: 'Get Active regions',
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
    #[Route('/api/region/get', name: 'app_region_get', methods: ['GET'])]
    public function get(): Response
    {
        $result = $this->crudRegion->Get();
        return new JsonResponse($result);
    }

    #[OA\Put(
        path: '/api/region/update',
        operationId: 'UpdateRegion',
        summary: 'update region',
        tags: ['Region']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'name', type: 'string'),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Add new region',
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
    #[Route('/api/region/update', name: 'app_region_update', methods: ['PUT'])]
    public function update(
        #[MapRequestPayload] UpdateCommonRequestDto $request
    ): Response {
        $result = $this->crudRegion->update($request);
        return new JsonResponse($result);
    }

    #[OA\Delete(
        path: '/api/region/delete/{id}',
        operationId: 'DeleteRegion',
        summary: 'delete region',
        tags: ['Region']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Add new region',
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
    #[Route('/api/region/delete/{id}', name: 'app_region_update', methods: ['DELETE'])]
    public function delete(int $id): Response
    {
        $result = $this->crudRegion->delete($id);
        return new JsonResponse($result);
    }

}
