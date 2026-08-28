<?php

namespace App\Domain\Services;

use App\Domain\Api\ApiResponse;
use App\Domain\Interfaces\CrudCityInterface;
use App\Dto\Request\City\CreateCityRequestDto;
use App\Dto\Request\City\UpdateCityRequestDto;
use App\Entity\City;
use App\Enums\Code;
use App\Repository\CityRepository;
use App\Repository\DepartmentRepository;
use Doctrine\ORM\EntityManagerInterface;

class CityService implements CrudCityInterface
{
    public function __construct(
        private readonly CityRepository $cityRepository,
        private readonly DepartmentRepository $departmentRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function add(CreateCityRequestDto $request): ApiResponse
    {
        $existingCity = $this->cityRepository->findOneBy(['name' => $request->name]);
        if (null !== $existingCity) {
            return ApiResponse::error(
                message: 'City already exists',
                code: Code::NOT_VALID->value
            );
        }

        $department = $this->departmentRepository->find($request->departmentId);
        if (null === $department) {
            return ApiResponse::error(
                message: 'Department cannot be found',
                code: Code::NOT_FOUND->value
            );
        }

        $cityToCreate = new City();
        $cityToCreate->setName($request->name);
        $cityToCreate->setDepartment($department);
        $cityToCreate->setActive(true);

        $this->entityManager->persist($cityToCreate);
        $this->entityManager->flush();

        return ApiResponse::success(
            message: 'City added successfully',
            code: Code::SUCCESS->value
        );
    }

    public function update(UpdateCityRequestDto $request): ApiResponse
    {
        $city = $this->cityRepository->find($request->id);
        if (null === $city) {
            return ApiResponse::error(
                message: 'City cannot be found',
                code: Code::NOT_FOUND->value
            );
        }

        $department = $this->departmentRepository->find($request->departmentId);
        if (null === $department) {
            return ApiResponse::error(
                message: 'Department cannot be found',
                code: Code::NOT_FOUND->value
            );
        }

        $city->setName($request->name);
        $city->setDepartment($department);

        $this->entityManager->persist($city);
        $this->entityManager->flush();

        return ApiResponse::success(
            message: 'City updated successfully',
            code: Code::UPDATED->value
        );
    }

    public function get(): ApiResponse
    {
        $cities = $this->cityRepository->findBy(['active' => true], ['name' => 'ASC']);
        if ([] === $cities) {
            return ApiResponse::error(
                message: 'No cities found',
                code: Code::NOT_FOUND->value
            );
        }

        return ApiResponse::success(
            message: 'Get cities successfully',
            code: Code::SUCCESS->value,
            data: array_map($this->mapCity(...), $cities)
        );
    }

    public function getByDepartment(int $departmentId): ApiResponse
    {
        $department = $this->departmentRepository->find($departmentId);
        if (null === $department) {
            return ApiResponse::error(
                message: 'Department cannot be found',
                code: Code::NOT_FOUND->value
            );
        }

        $cities = $this->cityRepository->findBy(
            ['department' => $department, 'active' => true],
            ['name' => 'ASC']
        );
        if ([] === $cities) {
            return ApiResponse::error(
                message: 'No cities found for this department',
                code: Code::NOT_FOUND->value
            );
        }

        return ApiResponse::success(
            message: 'Get cities successfully',
            code: Code::SUCCESS->value,
            data: array_map($this->mapCity(...), $cities)
        );
    }

    /**
     * @return array{id: int|null, name: string, departmentId: int|null, departmentName: string|null}
     */
    private function mapCity(City $city): array
    {
        return [
            'id' => $city->getId(),
            'name' => $city->getName(),
            'departmentId' => $city->getDepartment()->getId(),
            'departmentName' => $city->getDepartment()->getName(),
        ];
    }
}
