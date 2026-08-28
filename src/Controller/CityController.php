<?php

namespace App\Controller;

use App\Domain\Interfaces\CrudCityInterface;
use App\Dto\Request\City\CreateCityRequestDto;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class CityController extends AbstractController
{
   public function __construct(
       private readonly CrudCityInterface $crudCity,
   ) {
   }

   #[OA\Post(
       path: '/api/city/create',
       operationId: 'CreateCity',
       summary: 'Add new city',
        tags: ['city']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'departmentId', description: 'ID of the department', type: 'integer',),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Add new city',
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
    #[Route('/api/city/create', name: 'app_city_create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] CreateCityRequestDto $request
    ): Response {
        $result = $this->crudCity->add($request);

        return new JsonResponse($result);
    }

    #[OA\Get(
        path: '/api/city/get',
        operationId: 'GetCity',
        summary: 'Get active cities',
        tags: ['city']
    )]
    #[OA\Response(
        response: 200,
        description: 'Get Active cities',
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
    #[Route('/api/city/get', name: 'app_city_get', methods: ['GET'])]
    public function get(): Response
    {
        $result = $this->crudCity->get();
        return new JsonResponse($result);
    }

     #[OA\Get(
        path: '/api/city/getByDepartment/{departmentId}',
        operationId: 'GetCityByDepartment',
        summary: 'Get active cities by department',
        tags: ['city']
    )]
    #[OA\Response(
        response: 200,
        description: 'Get Active cities by department',
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
    #[Route('/api/city/getByDepartment/{departmentId}', name: 'app_city_get_by_department', methods: ['GET'])]
    public function getByDepartment(int $departmentId): Response
    {
        $result = $this->crudCity->getByDepartment($departmentId);
        return new JsonResponse($result);
    }

    }
