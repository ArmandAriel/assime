<?php

namespace App\Controller;

use App\Domain\Api\ApiValidator;
use App\Domain\Interfaces\ConversationInterface;
use App\Domain\Interfaces\CrudListingInterface;
use App\Domain\Interfaces\FavoriteInterface;
use App\Domain\Interfaces\ListingImageInterface;
use App\Dto\Request\Conversation\StartConversationRequestDto;
use App\Dto\Request\Listing\CreateListingRequestDto;
use App\Dto\Request\Listing\ListingSearchRequestDto;
use App\Dto\Request\Listing\UpdateListingRequestDto;
use App\Entity\User;
use App\Enums\ListingStatus;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class ListingController extends AbstractController
{
    public function __construct(
        private readonly CrudListingInterface $crudListing,
        private readonly ListingImageInterface $listingImage,
        private readonly FavoriteInterface $favorite,
        private readonly ConversationInterface $conversation,
        private readonly ApiValidator $apiValidator,
    ) {
    }

    #[Route('/listing', name: 'app_listing')]
    public function index(): Response
    {
        return $this->render('listing/index.html.twig', [
            'controller_name' => 'ListingController',
        ]);
    }

    #[OA\Post(
        path: '/api/listing/create',
        operationId: 'CreateListing',
        summary: 'Add new listing',
        tags: ['Listing']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'title', type: 'string'),
                new OA\Property(property: 'description', type: 'string'),
                new OA\Property(property: 'price', type: 'float'),
                new OA\Property(property: 'localisationDetails', type: 'string'),
                new OA\Property(property: 'categoryId', type: 'integer'),
                new OA\Property(property: 'cityId', type: 'integer'),
                new OA\Property(property: 'status', type: 'string', example: 'draft'),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Add new listing',
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
    #[Route('/api/listing/create', name: 'app_listing_create', methods: ['POST'])]
    public function create(Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        $dto = new CreateListingRequestDto(
            title: (string) ($payload['title'] ?? ''),
            description: (string) ($payload['description'] ?? ''),
            price: (float) ($payload['price'] ?? 0.0),
            localisationDetails: (string) ($payload['localisationDetails'] ?? ''),
            categoryId: (int) ($payload['categoryId'] ?? 0),
            cityId: (int) ($payload['cityId'] ?? 0),
            status: ListingStatus::tryFrom((string) ($payload['status'] ?? ListingStatus::Draft->value)) ?? ListingStatus::Draft,
        );

        if ($validationError = $this->apiValidator->validate($dto)) {
            return $this->json($validationError->toArray(), $validationError->code);
        }

        $result = $this->crudListing->Add($dto, $user);

        return $this->json($result->toArray(), $result->code);
    }

    #[OA\Get(
        path: '/api/listing/get',
        operationId: 'GetListing',
        summary: 'Search / list listings',
        tags: ['Listing']
    )]
    #[OA\Parameter(name: 'categoryId', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'cityId', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'ownerId', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'minPrice', in: 'query', required: false, schema: new OA\Schema(type: 'number'))]
    #[OA\Parameter(name: 'maxPrice', in: 'query', required: false, schema: new OA\Schema(type: 'number'))]
    #[OA\Parameter(name: 'q', in: 'query', required: false, schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(
        response: 200,
        description: 'Listings retrieved',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: 'true'),
                new OA\Property(property: 'message', type: 'string', example: 'operation succeeded'),
                new OA\Property(property: 'code', type: 'int', example: '200'),
                new OA\Property(property: 'data', type: 'object'),
            ],
            type: 'object'
        )
    )]
    #[Route('/api/listing/get', name: 'app_listing_get', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $status = $request->query->get('status');

        $dto = new ListingSearchRequestDto(
            categoryId: $request->query->has('categoryId') ? (int) $request->query->get('categoryId') : null,
            cityId: $request->query->has('cityId') ? (int) $request->query->get('cityId') : null,
            ownerId: $request->query->has('ownerId') ? (int) $request->query->get('ownerId') : null,
            status: null !== $status ? ListingStatus::tryFrom($status) : null,
            minPrice: $request->query->has('minPrice') ? (float) $request->query->get('minPrice') : null,
            maxPrice: $request->query->has('maxPrice') ? (float) $request->query->get('maxPrice') : null,
            q: $request->query->get('q'),
            page: (int) $request->query->get('page', 1),
            limit: (int) $request->query->get('limit', 20),
        );

        $result = $this->crudListing->Get($dto);

        return $this->json($result->toArray(), $result->code);
    }

    #[OA\Get(
        path: '/api/listing/get/{id}',
        operationId: 'GetListingById',
        summary: 'Get a single listing',
        tags: ['Listing']
    )]
    #[OA\Response(
        response: 200,
        description: 'Listing retrieved',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: 'true'),
                new OA\Property(property: 'message', type: 'string', example: 'operation succeeded'),
                new OA\Property(property: 'code', type: 'int', example: '200'),
                new OA\Property(property: 'data', type: 'object'),
            ],
            type: 'object'
        )
    )]
    #[Route('/api/listing/get/{id}', name: 'app_listing_get_by_id', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function get(int $id): JsonResponse
    {
        $result = $this->crudListing->GetById($id);

        return $this->json($result->toArray(), $result->code);
    }

    #[OA\Put(
        path: '/api/listing/update',
        operationId: 'UpdateListing',
        summary: 'Update a listing',
        tags: ['Listing']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'title', type: 'string'),
                new OA\Property(property: 'description', type: 'string'),
                new OA\Property(property: 'price', type: 'float'),
                new OA\Property(property: 'localisationDetails', type: 'string'),
                new OA\Property(property: 'categoryId', type: 'integer'),
                new OA\Property(property: 'cityId', type: 'integer'),
                new OA\Property(property: 'status', type: 'string', example: 'published'),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Listing updated',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: 'true'),
                new OA\Property(property: 'message', type: 'string', example: 'operation succeeded'),
                new OA\Property(property: 'code', type: 'int', example: '202'),
                new OA\Property(property: 'data', type: 'object'),
            ],
            type: 'object'
        )
    )]
    #[Route('/api/listing/update', name: 'app_listing_update', methods: ['PUT'])]
    public function update(Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        $dto = new UpdateListingRequestDto(
            id: (int) ($payload['id'] ?? 0),
            title: (string) ($payload['title'] ?? ''),
            description: (string) ($payload['description'] ?? ''),
            price: (float) ($payload['price'] ?? 0.0),
            localisationDetails: (string) ($payload['localisationDetails'] ?? ''),
            categoryId: (int) ($payload['categoryId'] ?? 0),
            cityId: (int) ($payload['cityId'] ?? 0),
            status: ListingStatus::tryFrom((string) ($payload['status'] ?? ListingStatus::Draft->value)) ?? ListingStatus::Draft,
        );

        if ($validationError = $this->apiValidator->validate($dto)) {
            return $this->json($validationError->toArray(), $validationError->code);
        }

        $result = $this->crudListing->Update($dto, $user);

        return $this->json($result->toArray(), $result->code);
    }

    #[OA\Delete(
        path: '/api/listing/delete/{id}',
        operationId: 'DeleteListing',
        summary: 'Delete a listing',
        tags: ['Listing']
    )]
    #[OA\Response(
        response: 200,
        description: 'Listing deleted',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: 'true'),
                new OA\Property(property: 'message', type: 'string', example: 'operation succeeded'),
                new OA\Property(property: 'code', type: 'int', example: '200'),
            ],
            type: 'object'
        )
    )]
    #[Route('/api/listing/delete/{id}', name: 'app_listing_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id, #[CurrentUser] User $user): JsonResponse
    {
        $result = $this->crudListing->Delete($id, $user);

        return $this->json($result->toArray(), $result->code);
    }

    #[OA\Post(
        path: '/api/listing/{id}/images',
        operationId: 'UploadListingImage',
        summary: 'Upload an image for a listing (multipart/form-data, field name "image")',
        tags: ['Listing']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'image', type: 'string', format: 'binary'),
                ],
                type: 'object'
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Image uploaded',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: 'true'),
                new OA\Property(property: 'message', type: 'string', example: 'operation succeeded'),
                new OA\Property(property: 'code', type: 'int', example: '201'),
                new OA\Property(property: 'data', type: 'object'),
            ],
            type: 'object'
        )
    )]
    #[Route('/api/listing/{id}/images', name: 'app_listing_image_upload', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function uploadImage(int $id, Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $file = $request->files->get('image');
        if (null === $file) {
            return $this->json([
                'success' => false,
                'message' => 'Aucun fichier envoye (champ attendu: "image")',
                'code' => 407,
                'data' => [],
                'errors' => [],
            ], 407);
        }

        $result = $this->listingImage->UploadImage($id, $file, $user);

        return $this->json($result->toArray(), $result->code);
    }

    #[OA\Delete(
        path: '/api/listing/{id}/images/{imageId}',
        operationId: 'DeleteListingImage',
        summary: 'Delete an image from a listing',
        tags: ['Listing']
    )]
    #[OA\Response(
        response: 200,
        description: 'Image deleted',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: 'true'),
                new OA\Property(property: 'message', type: 'string', example: 'operation succeeded'),
                new OA\Property(property: 'code', type: 'int', example: '200'),
            ],
            type: 'object'
        )
    )]
    #[Route('/api/listing/{id}/images/{imageId}', name: 'app_listing_image_delete', methods: ['DELETE'], requirements: ['id' => '\d+', 'imageId' => '\d+'])]
    public function deleteImage(int $id, int $imageId, #[CurrentUser] User $user): JsonResponse
    {
        $result = $this->listingImage->DeleteImage($id, $imageId, $user);

        return $this->json($result->toArray(), $result->code);
    }

    #[OA\Post(
        path: '/api/listing/{id}/favorite',
        operationId: 'AddFavorite',
        summary: 'Add a listing to the current user favorites',
        tags: ['Favorite']
    )]
    #[OA\Response(response: 200, description: 'Added to favorites')]
    #[Route('/api/listing/{id}/favorite', name: 'app_listing_favorite_add', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function addFavorite(int $id, #[CurrentUser] User $user): JsonResponse
    {
        $result = $this->favorite->Add($id, $user);

        return $this->json($result->toArray(), $result->code);
    }

    #[OA\Delete(
        path: '/api/listing/{id}/favorite',
        operationId: 'RemoveFavorite',
        summary: 'Remove a listing from the current user favorites',
        tags: ['Favorite']
    )]
    #[OA\Response(response: 200, description: 'Removed from favorites')]
    #[Route('/api/listing/{id}/favorite', name: 'app_listing_favorite_remove', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function removeFavorite(int $id, #[CurrentUser] User $user): JsonResponse
    {
        $result = $this->favorite->Remove($id, $user);

        return $this->json($result->toArray(), $result->code);
    }

    #[OA\Post(
        path: '/api/listing/{id}/conversations',
        operationId: 'StartConversation',
        summary: 'Start (or continue) a conversation with the seller about a listing',
        tags: ['Conversation']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [new OA\Property(property: 'message', type: 'string')]
        )
    )]
    #[OA\Response(response: 200, description: 'Conversation started')]
    #[Route('/api/listing/{id}/conversations', name: 'app_listing_conversation_start', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function startConversation(int $id, Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        $dto = new StartConversationRequestDto(
            message: (string) ($payload['message'] ?? ''),
        );

        if ($validationError = $this->apiValidator->validate($dto)) {
            return $this->json($validationError->toArray(), $validationError->code);
        }

        $result = $this->conversation->Start($id, $dto, $user);

        return $this->json($result->toArray(), $result->code);
    }
}
