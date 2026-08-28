<?php

namespace App\Domain\Services;

use App\Domain\Api\ApiResponse;
use App\Domain\Interfaces\CrudDepartmentInterface;
use App\Dto\Request\Common\UpdateCommonRequestDto;
use App\Dto\Request\DepartmentDto\CreateDepartmentRequestDto;
use App\Entity\Department;
use App\Enums\Code;
use App\Repository\DepartmentRepository;
use App\Repository\RegionRepository;
use Doctrine\ORM\EntityManagerInterface;

class DepartmentService implements CrudDepartmentInterface
{
    public function __construct(
        private readonly DepartmentRepository $departmentRepository,
        private readonly RegionRepository $regionRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function Add(CreateDepartmentRequestDto $request): ApiResponse
    {
        if (null === $request) {
            return ApiResponse::error(
                message: 'Parameter must not be null',
                code: Code::NOT_VALID->value
            );
        }

        $existingDepartment = $this->departmentRepository->findOneBy(['name' => $request->name]);
        if (null !== $existingDepartment) {
            return ApiResponse::error(
                message: 'Region already exists',
                code: Code::NOT_VALID->value
            );
        }

        $region = $this->regionRepository->find($request->regionId);

        if (null === $region) {
            return ApiResponse::error(
                message: 'Region cannot be found',
                code: Code::NOT_FOUND->value
            );
        }

        $departmentToCreate = new Department(
            $request->name,
            $region,
        );

        $this->entityManager->persist($departmentToCreate);
        $this->entityManager->flush();
        return ApiResponse::success(
            message: 'Department added successfully',
            code: Code::SUCCESS->value
        );
    }

    public function Update(UpdateCommonRequestDto $request): ApiResponse
    {
        // TODO: Implement Update() method.
    }
    //
    //    public function Delete(int $id): ApiResponse
    //    {
    //        // TODO: Implement Delete() method.
    //    }
    //
    public function Get(): ApiResponse
    {
        $list = $this->departmentRepository->findBy(['active' => true], ['name' => 'ASC']);
        if (null === $list) {
            return ApiResponse::error(
                message: 'No regions found',
                code: Code::NOT_FOUND->value
            );
        }

        $data = array_map(
            fn ($department) => [
                'id' => $department->getId(),
                'name' => $department->getName(),
                'regionId' => $department->getRegion()->getId(),
                'regionName' => $department->getRegion()->getName(),
            ],
            $list
        );

        return ApiResponse::success(
            message: 'Get regions successfully',
            code: Code::SUCCESS->value,
            data: $data
        );
    }
}
