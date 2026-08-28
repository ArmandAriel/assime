<?php

namespace App\Controller;

use App\Domain\Interfaces\FavoriteInterface;
use App\Entity\User;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class FavoriteController extends AbstractController
{
    public function __construct(
        private readonly FavoriteInterface $favorite,
    ) {
    }

    #[OA\Get(
        path: '/api/favorites',
        operationId: 'GetMyFavorites',
        summary: 'Get the current user favorite listings',
        tags: ['Favorite']
    )]
    #[OA\Response(response: 200, description: 'Favorites retrieved')]
    #[Route('/api/favorites', name: 'app_favorites_get', methods: ['GET'])]
    public function index(#[CurrentUser] User $user): JsonResponse
    {
        $result = $this->favorite->GetMine($user);

        return $this->json($result->toArray(), $result->code);
    }
}
