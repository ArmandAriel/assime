<?php

namespace App\Domain\Services;

use App\Domain\Api\ApiResponse;
use App\Domain\Interfaces\ListingImageInterface;
use App\Entity\Image;
use App\Entity\User;
use App\Enums\Code;
use App\Repository\ImageRepository;
use App\Repository\ListingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ListingImageService implements ListingImageInterface
{
    private const int MAX_FILE_SIZE = 5 * 1024 * 1024; // 5 MB

    /**
     * @var list<string>
     */
    private const array ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    public function __construct(
        private readonly ListingRepository $listingRepository,
        private readonly ImageRepository $imageRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly Filesystem $filesystem,
        private readonly string $listingImagesDir,
        private readonly string $listingImagesPublicPath,
    ) {
    }

    public function UploadImage(int $listingId, UploadedFile $file, User $currentUser): ApiResponse
    {
        $listing = $this->listingRepository->find($listingId);
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

        if (!$file->isValid()) {
            return ApiResponse::error(
                message: 'Le fichier envoye est invalide',
                code: Code::NOT_VALID->value,
            );
        }

        if ($file->getSize() > self::MAX_FILE_SIZE) {
            return ApiResponse::error(
                message: 'Le fichier depasse la taille maximale autorisee (5 Mo)',
                code: Code::NOT_VALID->value,
            );
        }

        if (!in_array($file->getMimeType(), self::ALLOWED_MIME_TYPES, true)) {
            return ApiResponse::error(
                message: 'Format de fichier non supporte (jpeg, png, webp, gif uniquement)',
                code: Code::NOT_VALID->value,
            );
        }

        $extension = $file->guessExtension() ?? 'bin';
        $filename = bin2hex(random_bytes(16)).'.'.$extension;
        $targetDir = rtrim($this->listingImagesDir, '/\\').'/'.$listingId;

        $this->filesystem->mkdir($targetDir);
        $file->move($targetDir, $filename);

        $nextPosition = 1;
        foreach ($listing->getImages() as $existingImage) {
            $nextPosition = max($nextPosition, $existingImage->getPosition() + 1);
        }

        $image = new Image();
        $image->setPath(rtrim($this->listingImagesPublicPath, '/').'/'.$listingId.'/'.$filename);
        $image->setPosition($nextPosition);
        $image->setListing($listing);

        $this->entityManager->persist($image);
        $this->entityManager->flush();

        return ApiResponse::success(
            message: 'Image ajoutee avec succes',
            code: Code::CREATED->value,
            data: [
                'id' => $image->getId(),
                'path' => $image->getPath(),
                'position' => $image->getPosition(),
            ]
        );
    }

    public function DeleteImage(int $listingId, int $imageId, User $currentUser): ApiResponse
    {
        $image = $this->imageRepository->find($imageId);
        if (null === $image || null === $image->getListing() || $image->getListing()->getId() !== $listingId) {
            return ApiResponse::error(
                message: 'Image introuvable',
                code: Code::NOT_FOUND->value,
            );
        }

        if ($image->getListing()->getOwner()->getId() !== $currentUser->getId()) {
            return ApiResponse::error(
                message: "Vous n'etes pas autorise a modifier cette annonce",
                code: Code::FORBIDDEN->value,
            );
        }

        $absolutePath = rtrim($this->listingImagesDir, '/\\').'/'.$listingId.'/'.basename($image->getPath());
        if ($this->filesystem->exists($absolutePath)) {
            $this->filesystem->remove($absolutePath);
        }

        $this->entityManager->remove($image);
        $this->entityManager->flush();

        return ApiResponse::success(
            message: 'Image supprimee avec succes',
            code: Code::SUCCESS->value,
        );
    }
}
