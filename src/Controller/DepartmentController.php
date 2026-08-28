<?php

namespace App\Controller;

use App\Domain\Interfaces\CrudDepartmentInterface;
use App\Dto\Request\Common\UpdateCommonRequestDto;
use App\Dto\Request\DepartmentDto\CreateDepartmentRequestDto;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class DepartmentController extends AbstractController
{
    public function __construct(
        private readonly CrudDepartmentInterface $crudDepartment,
    ) {
    }

    #[OA\Post(
        path: '/api/department/create',
        operationId: 'CreateDepartment',
        summary: 'Add new department',
        tags: ['department']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'regionId', description: 'ID of the region', type: 'integer',),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Add new department',
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
    #[Route('/api/department/create', name: 'app_department_create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload]  CreateDepartmentRequestDto $request
    ): Response {
        $result = $this->crudDepartment->add($request);
        return new JsonResponse($result);
    }



    #[OA\Get(
        path: '/api/department/get',
        operationId: 'Getdepartment',
        summary: 'Get active departments',
        tags: ['department']
    )]
    #[OA\Response(
        response: 200,
        description: 'Get Active departments',
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
    #[Route('/api/department/get', name: 'app_department_get', methods: ['GET'])]
    public function get(): Response
    {
        $result = $this->crudDepartment->get();
        return new JsonResponse($result);
    }

    #[OA\Put(
        path: '/api/department/update',
        operationId: 'Updatedepartment',
        summary: 'update department',
        tags: ['department']
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
        description: 'Add new department',
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
    #[Route('/api/department/update', name: 'app_department_update', methods: ['PUT'])]
    public function update(
        #[MapRequestPayload] UpdateCommonRequestDto $request
    ): Response {
        $result = $this->crudDepartment->update($request);
        return new JsonResponse($result);
    }

    #[OA\Delete(
        path: '/api/department/delete/{id}',
        operationId: 'Deletedepartment',
        summary: 'delete department',
        tags: ['department']
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
        description: 'Add new department',
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
    #[Route('/api/department/delete/{id}', name: 'app_department_update', methods: ['DELETE'])]
    public function delete(int $id): Response
    {
        //$result = $this->crudDepartment->delete($id);
        return new JsonResponse('Not implemented');
    }
}
