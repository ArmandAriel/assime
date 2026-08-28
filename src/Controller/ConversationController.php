<?php

namespace App\Controller;

use App\Domain\Api\ApiValidator;
use App\Domain\Interfaces\ConversationInterface;
use App\Dto\Request\Conversation\SendMessageRequestDto;
use App\Entity\User;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class ConversationController extends AbstractController
{
    public function __construct(
        private readonly ConversationInterface $conversation,
        private readonly ApiValidator $apiValidator,
    ) {
    }

    #[OA\Get(
        path: '/api/conversations',
        operationId: 'GetMyConversations',
        summary: 'Get the current user conversations (as buyer or seller)',
        tags: ['Conversation']
    )]
    #[OA\Response(response: 200, description: 'Conversations retrieved')]
    #[Route('/api/conversations', name: 'app_conversations_get', methods: ['GET'])]
    public function index(#[CurrentUser] User $user): JsonResponse
    {
        $result = $this->conversation->GetMine($user);

        return $this->json($result->toArray(), $result->code);
    }

    #[OA\Get(
        path: '/api/conversations/{id}/messages',
        operationId: 'GetConversationMessages',
        summary: 'Get all messages in a conversation (participants only)',
        tags: ['Conversation']
    )]
    #[OA\Response(response: 200, description: 'Messages retrieved')]
    #[Route('/api/conversations/{id}/messages', name: 'app_conversation_messages_get', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function messages(int $id, #[CurrentUser] User $user): JsonResponse
    {
        $result = $this->conversation->GetMessages($id, $user);

        return $this->json($result->toArray(), $result->code);
    }

    #[OA\Post(
        path: '/api/conversations/{id}/messages',
        operationId: 'SendConversationMessage',
        summary: 'Send a message in an existing conversation (participants only)',
        tags: ['Conversation']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [new OA\Property(property: 'content', type: 'string')]
        )
    )]
    #[OA\Response(response: 200, description: 'Message sent')]
    #[Route('/api/conversations/{id}/messages', name: 'app_conversation_messages_send', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function sendMessage(int $id, Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        $dto = new SendMessageRequestDto(
            content: (string) ($payload['content'] ?? ''),
        );

        if ($validationError = $this->apiValidator->validate($dto)) {
            return $this->json($validationError->toArray(), $validationError->code);
        }

        $result = $this->conversation->SendMessage($id, $dto, $user);

        return $this->json($result->toArray(), $result->code);
    }
}
