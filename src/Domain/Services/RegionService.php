<?php

namespace App\Domain\Services;

use App\Domain\Api\ApiResponse;
use App\Domain\Interfaces\CrudRegionInterface;
use App\Dto\Request\Common\CreateCommonRequestDto;
use App\Dto\Request\Common\UpdateCommonRequestDto;
use App\Entity\Region;
use App\Enums\Code;
use App\Repository\RegionRepository;
use Doctrine\ORM\EntityManagerInterface;

class RegionService implements crudRegionInterface
{
    public function __construct(
        private RegionRepository $regionRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function Add(CreateCommonRequestDto $request): ApiResponse
    {
        if (null === $request) {
            return ApiResponse::error(
                message: 'Parameter must not be null',
                code: Code::NOT_VALID->value
            );
        }

        $existingRegion = $this->regionRepository->findOneBy(['name' => $request->name]);
        if (null !== $existingRegion) {
            return ApiResponse::error(
                message: 'Region already exists',
                code: Code::NOT_VALID->value
            );
        }

        $regionToCreate = new Region($request->name);
        $this->entityManager->persist($regionToCreate);
        $this->entityManager->flush();
        return ApiResponse::success(
            message: 'Region added successfully',
            code: Code::SUCCESS->value
        );
    }

    public function Get(): ApiResponse
    {
        $list = $this->regionRepository->findBy(['active' => true], ['name' => 'ASC']);
        if (null === $list) {
            return ApiResponse::error(
                message: 'No regions found',
                code: Code::NOT_FOUND->value
            );
        }

        $data = array_map(
            fn ($region) => [
                'id' => $region->getId(),
                'name' => $region->getName(),
                ],
            $list
        );

        return ApiResponse::success(
            message: 'Get regions successfully',
            code: Code::SUCCESS->value,
            data: $data
        );
    }


    public function Update(UpdateCommonRequestDto $request): ApiResponse
    {
        if (null === $request) {
            return ApiResponse::error(
                message: 'Parameter must not be null',
                code: Code::NOT_VALID->value
            );
        }

        $existingRegion = $this->regionRepository->find($request->id);
        if (null === $existingRegion) {
            return ApiResponse::error(
                message: 'Region cannot be found',
                code: Code::NOT_FOUND->value
            );
        }

        $existingRegion->setName($request->name);
        $this->entityManager->persist($existingRegion);
        $this->entityManager->flush();

        return ApiResponse::success(
            message: 'Region updated successfully',
            code: Code::SUCCESS->value,
        );
    }


    public function Delete(int $id): ApiResponse
    {
        if (null === $id) {
            return ApiResponse::error(
                message: 'Parameter must not be null',
                code: Code::NOT_VALID->value
            );
        }

        $regionToDelete = $this->regionRepository->find($id);
        if (null === $regionToDelete) {
            return ApiResponse::error(
                message: 'Region cannot be found',
                code: Code::NOT_FOUND->value
            );
        }

        $regionToDelete->setActive(false);
        $this->entityManager->persist($regionToDelete);
        $this->entityManager->flush();

        return ApiResponse::success(
            message: 'Region deleted successfully',
            code: Code::SUCCESS->value,
        );
    }
}
