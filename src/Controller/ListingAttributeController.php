<?php

namespace App\Controller;

use App\Domain\Interfaces\ListingAttributeInterface;
use App\Dto\Request\Listing\ListingAttributeValueInput;
use App\Dto\Request\Listing\SetListingAttributesRequestDto;
use App\Entity\User;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class ListingAttributeController extends AbstractController
{
    public function __construct(
        private readonly ListingAttributeInterface $listingAttribute,
    ) {
    }

    #[OA\Get(
        path: '/api/listing/{id}/attributes',
        operationId: 'GetListingAttributes',
        summary: 'Get the category-specific attribute values of a listing (e.g. bedrooms, vehicle brand...)',
        tags: ['Listing']
    )]
    #[OA\Response(response: 200, description: 'Attribute values retrieved')]
    #[Route('/api/listing/{id}/attributes', name: 'app_listing_attributes_get', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function get(int $id): JsonResponse
    {
        $result = $this->listingAttribute->GetValues($id);

        return $this->json($result->toArray(), $result->code);
    }

    #[OA\Put(
        path: '/api/listing/{id}/attributes',
        operationId: 'SetListingAttributes',
        summary: 'Replace the category-specific attribute values of a listing (owner only)',
        tags: ['Listing']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'values',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'categoryAttributeId', type: 'integer'),
                            new OA\Property(property: 'value', type: 'string'),
                        ]
                    )
                ),
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Attribute values updated')]
    #[Route('/api/listing/{id}/attributes', name: 'app_listing_attributes_set', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function set(int $id, Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $rawValues = is_array($payload['values'] ?? null) ? $payload['values'] : [];

        $values = array_map(
            static fn (array $item) => new ListingAttributeValueInput(
                categoryAttributeId: (int) ($item['categoryAttributeId'] ?? 0),
                value: isset($item['value']) && '' !== $item['value'] ? (string) $item['value'] : null,
            ),
            $rawValues
        );

        $dto = new SetListingAttributesRequestDto(values: $values);

        $result = $this->listingAttribute->SetValues($id, $dto, $user);

        return $this->json($result->toArray(), $result->code);
    }
}
