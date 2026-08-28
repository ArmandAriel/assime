<?php

namespace App\Domain\Services;

use App\Domain\Api\ApiResponse;
use App\Domain\Interfaces\ConversationInterface;
use App\Dto\Request\Conversation\SendMessageRequestDto;
use App\Dto\Request\Conversation\StartConversationRequestDto;
use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
use App\Enums\Code;
use App\Repository\ConversationRepository;
use App\Repository\ListingRepository;
use Doctrine\ORM\EntityManagerInterface;

class ConversationService implements ConversationInterface
{
    public function __construct(
        private readonly ListingRepository $listingRepository,
        private readonly ConversationRepository $conversationRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function Start(int $listingId, StartConversationRequestDto $request, User $buyer): ApiResponse
    {
        $listing = $this->listingRepository->find($listingId);
        if (null === $listing) {
            return ApiResponse::error(
                message: 'Annonce introuvable',
                code: Code::NOT_FOUND->value,
            );
        }

        if ($listing->getOwner()->getId() === $buyer->getId()) {
            return ApiResponse::error(
                message: 'Vous ne pouvez pas demarrer une conversation sur votre propre annonce',
                code: Code::NOT_VALID->value,
            );
        }

        $content = trim($request->message);
        if ('' === $content) {
            return ApiResponse::error(
                message: 'Le message ne peut pas etre vide',
                code: Code::NOT_VALID->value,
            );
        }

        $conversation = $this->conversationRepository->findOneByListingAndBuyer($listing, $buyer);
        if (null === $conversation) {
            $conversation = new Conversation($listing, $buyer, $listing->getOwner());
            $this->entityManager->persist($conversation);
        }

        $message = new Message($buyer, $content);
        $conversation->addMessage($message);
        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return ApiResponse::success(
            message: 'Conversation demarree avec succes',
            code: Code::CREATED->value,
            data: $this->mapConversation($conversation, $buyer)
        );
    }

    public function GetMine(User $user): ApiResponse
    {
        $conversations = $this->conversationRepository->findForParticipant($user);

        $data = array_map(
            fn (Conversation $conversation) => $this->mapConversation($conversation, $user),
            $conversations
        );

        return ApiResponse::success(
            message: 'Conversations recuperees avec succes',
            code: Code::SUCCESS->value,
            data: $data
        );
    }

    public function GetMessages(int $conversationId, User $user): ApiResponse
    {
        $conversation = $this->conversationRepository->find($conversationId);
        if (null === $conversation) {
            return ApiResponse::error(
                message: 'Conversation introuvable',
                code: Code::NOT_FOUND->value,
            );
        }

        if (!$conversation->hasParticipant($user)) {
            return ApiResponse::error(
                message: "Vous n'etes pas autorise a consulter cette conversation",
                code: Code::FORBIDDEN->value,
            );
        }

        $messages = $conversation->getMessages();
        foreach ($messages as $message) {
            if ($message->getSender()->getId() !== $user->getId() && !$message->isRead()) {
                $message->setRead(true);
            }
        }
        $this->entityManager->flush();

        return ApiResponse::success(
            message: 'Messages recuperes avec succes',
            code: Code::SUCCESS->value,
            data: array_map($this->mapMessage(...), $messages->toArray())
        );
    }

    public function SendMessage(int $conversationId, SendMessageRequestDto $request, User $user): ApiResponse
    {
        $conversation = $this->conversationRepository->find($conversationId);
        if (null === $conversation) {
            return ApiResponse::error(
                message: 'Conversation introuvable',
                code: Code::NOT_FOUND->value,
            );
        }

        if (!$conversation->hasParticipant($user)) {
            return ApiResponse::error(
                message: "Vous n'etes pas autorise a repondre a cette conversation",
                code: Code::FORBIDDEN->value,
            );
        }

        $content = trim($request->content);
        if ('' === $content) {
            return ApiResponse::error(
                message: 'Le message ne peut pas etre vide',
                code: Code::NOT_VALID->value,
            );
        }

        $message = new Message($user, $content);
        $conversation->addMessage($message);
        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return ApiResponse::success(
            message: 'Message envoye avec succes',
            code: Code::CREATED->value,
            data: $this->mapMessage($message)
        );
    }

    /**
     * @return array{
     *     id: int|null,
     *     listingId: int|null,
     *     listingTitle: string,
     *     buyerId: int|null,
     *     sellerId: int|null,
     *     unreadCount: int,
     *     lastMessage: array{content: string, senderId: int|null, createdAt: string}|null,
     *     createdAt: string
     * }
     */
    private function mapConversation(Conversation $conversation, User $currentUser): array
    {
        $messages = $conversation->getMessages();
        $lastMessage = $messages->last();

        $unreadCount = 0;
        foreach ($messages as $message) {
            if ($message->getSender()->getId() !== $currentUser->getId() && !$message->isRead()) {
                ++$unreadCount;
            }
        }

        return [
            'id' => $conversation->getId(),
            'listingId' => $conversation->getListing()->getId(),
            'listingTitle' => $conversation->getListing()->getTitle(),
            'buyerId' => $conversation->getBuyer()->getId(),
            'sellerId' => $conversation->getSeller()->getId(),
            'unreadCount' => $unreadCount,
            'lastMessage' => false !== $lastMessage ? [
                'content' => $lastMessage->getContent(),
                'senderId' => $lastMessage->getSender()->getId(),
                'createdAt' => $lastMessage->getCreatedAt()->format(\DateTimeInterface::ATOM),
            ] : null,
            'createdAt' => $conversation->getCreatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }

    /**
     * @return array{id: int|null, senderId: int|null, content: string, isRead: bool, createdAt: string}
     */
    private function mapMessage(Message $message): array
    {
        return [
            'id' => $message->getId(),
            'senderId' => $message->getSender()->getId(),
            'content' => $message->getContent(),
            'isRead' => $message->isRead(),
            'createdAt' => $message->getCreatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }
}
