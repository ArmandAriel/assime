<?php

namespace App\Domain\Interfaces;

use App\Domain\Api\ApiResponse;
use App\Dto\Request\Conversation\SendMessageRequestDto;
use App\Dto\Request\Conversation\StartConversationRequestDto;
use App\Entity\User;

interface ConversationInterface
{
    public function Start(int $listingId, StartConversationRequestDto $request, User $buyer): ApiResponse;
    public function GetMine(User $user): ApiResponse;
    public function GetMessages(int $conversationId, User $user): ApiResponse;
    public function SendMessage(int $conversationId, SendMessageRequestDto $request, User $user): ApiResponse;
}
