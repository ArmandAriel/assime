<?php

namespace App\Domain\Services;

use App\Domain\Api\ApiResponse;
use App\Domain\Interfaces\CrudListingInterface;
use App\Dto\Request\Listing\CreateListingRequestDto;
use App\Dto\Request\Listing\ListingSearchRequestDto;
use App\Dto\Request\Listing\UpdateListingRequestDto;
use App\Entity\Listing;
use App\Entity\User;
use App\Enums\Code;
use App\Enums\ListingStatus;
use App\Repository\CityRepository;
use App\Repository\CategoryRepository;
use App\Repository\ListingRepository;
use Doctrine\ORM\EntityManagerInterface;

class ListingService implements CrudListingInterface
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly CityRepository $cityRepository,
        private readonly ListingRepository $listingRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function Add(CreateListingRequestDto $request, User $owner): ApiResponse
    {
        $category = $this->categoryRepository->find($request->categoryId);
        if (null === $category) {
            return ApiResponse::error(
                message: "La categorie n'existe pas",
                code: Code::NOT_FOUND->value,
            );
        }

        $city = $this->cityRepository->find($request->cityId);
        if (null === $city) {
            return ApiResponse::error(
                message: "La ville n'existe pas",
                code: Code::NOT_FOUND->value,
            );
        }

        $listingToCreate = new Listing(
            $request->title,
            $request->description,
            $request->price,
            $request->localisationDetails,
            $category,
            $city,
            $owner,
            $request->status,
        );

        $this->entityManager->persist($listingToCreate);
        $this->entityManager->flush();

        return ApiResponse::success(
            message: 'Annonce créée avec succès',
            code: Code::CREATED->value,
            data: $this->mapListing($listingToCreate)
        );
    }

    public function Update(UpdateListingRequestDto $request, User $currentUser): ApiResponse
    {
        $listing = $this->listingRepository->find($request->id);
        if (null === $listing) {
            return ApiResponse::error(
                message: 'Annonce introuvable',
                code: Code::NOT_FOUND->value,
            );
        }

        if ($listing->getOwner()->getId() !== $currentUser->getId()) {
            return ApiResponse::error(
                message: "Vous n'etes pas autorise a modifier cette annonce",
                code: Code::FORBIDDEN->value,
            );
        }

        $category = $this->categoryRepository->find($request->categoryId);
        if (null === $category) {
            return ApiResponse::error(
                message: "La categorie n'existe pas",
                code: Code::NOT_FOUND->value,
            );
        }

        $city = $this->cityRepository->find($request->cityId);
        if (null === $city) {
            return ApiResponse::error(
                message: "La ville n'existe pas",
                code: Code::NOT_FOUND->value,
            );
        }

        $listing
            ->setTitle($request->title)
            ->setDescription($request->description)
            ->setPrice($request->price)
            ->setLocalisationDetails($request->localisationDetails)
            ->setCategory($category)
            ->setCity($city)
            ->setStatus($request->status)
            ->setUpdatedAt(new \DateTime());

        $this->entityManager->flush();

        return ApiResponse::success(
            message: 'Annonce mise à jour avec succès',
            code: Code::UPDATED->value,
            data: $this->mapListing($listing)
        );
    }

    public function Get(ListingSearchRequestDto $filters): ApiResponse
    {
        $result = $this->listingRepository->search($filters);

        return ApiResponse::success(
            message: 'Annonces recuperees avec succes',
            code: Code::SUCCESS->value,
            data: [
                'items' => array_map($this->mapListing(...), $result['items']),
                'total' => $result['total'],
                'page' => max(1, $filters->page),
                'limit' => max(1, min(100, $filters->limit)),
            ]
        );
    }

    public function GetById(int $id): ApiResponse
    {
        $listing = $this->listingRepository->find($id);
        if (null === $listing) {
            return ApiResponse::error(
                message: 'Annonce introuvable',
                code: Code::NOT_FOUND->value,
            );
        }

        return ApiResponse::success(
            message: 'Annonce recuperee avec succes',
            code: Code::SUCCESS->value,
            data: $this->mapListing($listing)
        );
    }

    public function Delete(int $id, User $currentUser): ApiResponse
    {
        $listing = $this->listingRepository->find($id);
        if (null === $listing) {
            return ApiResponse::error(
                message: 'Annonce introuvable',
                code: Code::NOT_FOUND->value,
            );
        }

        if ($listing->getOwner()->getId() !== $currentUser->getId()) {
            return ApiResponse::error(
                message: "Vous n'etes pas autorise a supprimer cette annonce",
                code: Code::FORBIDDEN->value,
            );
        }

        $listing
            ->setStatus(ListingStatus::Deleted)
            ->setUpdatedAt(new \DateTime());

        $this->entityManager->flush();

        return ApiResponse::success(
            message: 'Annonce supprimée avec succès',
            code: Code::SUCCESS->value,
        );
    }

    /**
     * @return array{
     *     id: int|null,
     *     title: string,
     *     description: string,
     *     price: float|null,
     *     localisationDetails: string,
     *     categoryId: int|null,
     *     cityId: int|null,
     *     ownerId: int|null,
     *     ownerDisplayName: string,
     *     status: string,
     *     images: list<array{id: int|null, path: string, position: int}>,
     *     createdAt: string,
     *     updatedAt: string|null
     * }
     */
    private function mapListing(Listing $listing): array
    {
        $images = $listing->getImages()->toArray();
        usort($images, static fn ($a, $b) => $a->getPosition() <=> $b->getPosition());

        return [
            'id' => $listing->getId(),
            'title' => $listing->getTitle(),
            'description' => $listing->getDescription(),
            'price' => $listing->getPrice(),
            'localisationDetails' => $listing->getLocalisationDetails(),
            'categoryId' => $listing->getCategory()->getId(),
            'cityId' => $listing->getCity()->getId(),
            'ownerId' => $listing->getOwner()->getId(),
            'ownerDisplayName' => $listing->getOwner()->getDisplayName() ?? $listing->getOwner()->getEmail(),
            'status' => $listing->getStatus()->value,
            'images' => array_map(
                static fn ($image) => [
                    'id' => $image->getId(),
                    'path' => $image->getPath(),
                    'position' => $image->getPosition(),
                ],
                $images
            ),
            'createdAt' => $listing->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'updatedAt' => $listing->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
        ];
    }
}
