<?php

namespace App\Domain\Services;

use App\Domain\Api\ApiResponse;
use App\Domain\Interfaces\FavoriteInterface;
use App\Entity\Favorite;
use App\Entity\User;
use App\Enums\Code;
use App\Repository\FavoriteRepository;
use App\Repository\ListingRepository;
use Doctrine\ORM\EntityManagerInterface;

class FavoriteService implements FavoriteInterface
{
    public function __construct(
        private readonly ListingRepository $listingRepository,
        private readonly FavoriteRepository $favoriteRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function Add(int $listingId, User $user): ApiResponse
    {
        $listing = $this->listingRepository->find($listingId);
        if (null === $listing) {
            return ApiResponse::error(
                message: 'Annonce introuvable',
                code: Code::NOT_FOUND->value,
            );
        }

        $existing = $this->favoriteRepository->findOneByUserAndListing($user, $listing);
        if (null !== $existing) {
            return ApiResponse::success(
                message: 'Annonce deja dans les favoris',
                code: Code::SUCCESS->value,
            );
        }

        $favorite = new Favorite($user, $listing);
        $this->entityManager->persist($favorite);
        $this->entityManager->flush();

        return ApiResponse::success(
            message: 'Annonce ajoutee aux favoris',
            code: Code::CREATED->value,
        );
    }

    public function Remove(int $listingId, User $user): ApiResponse
    {
        $listing = $this->listingRepository->find($listingId);
        if (null === $listing) {
            return ApiResponse::error(
                message: 'Annonce introuvable',
                code: Code::NOT_FOUND->value,
            );
        }

        $existing = $this->favoriteRepository->findOneByUserAndListing($user, $listing);
        if (null !== $existing) {
            $this->entityManager->remove($existing);
            $this->entityManager->flush();
        }

        return ApiResponse::success(
            message: 'Annonce retiree des favoris',
            code: Code::SUCCESS->value,
        );
    }

    public function GetMine(User $user): ApiResponse
    {
        $favorites = $this->favoriteRepository->findByUser($user);

        $data = array_map(
            static function (Favorite $favorite): array {
                $listing = $favorite->getListing();
                $firstImage = $listing->getImages()->first();

                return [
                    'favoriteId' => $favorite->getId(),
                    'favoritedAt' => $favorite->getCreatedAt()->format(\DateTimeInterface::ATOM),
                    'listing' => [
                        'id' => $listing->getId(),
                        'title' => $listing->getTitle(),
                        'price' => $listing->getPrice(),
                        'categoryId' => $listing->getCategory()->getId(),
                        'cityId' => $listing->getCity()->getId(),
                        'status' => $listing->getStatus()->value,
                        'thumbnail' => false !== $firstImage ? $firstImage->getPath() : null,
                    ],
                ];
            },
            $favorites
        );

        return ApiResponse::success(
            message: 'Favoris recuperes avec succes',
            code: Code::SUCCESS->value,
            data: $data
        );
    }
}
