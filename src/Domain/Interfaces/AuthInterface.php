<?php

namespace App\Domain\Interfaces;

use App\Domain\Api\ApiResponse;
use App\Dto\Request\User\ForgotPasswordRequestDto;
use App\Dto\Request\User\RegisterUserRequestDto;
use App\Dto\Request\User\ResetPasswordRequestDto;
use App\Entity\User;

interface AuthInterface
{
    public function Register(RegisterUserRequestDto $request): ApiResponse;
    public function Me(User $user): ApiResponse;
    public function ForgotPassword(ForgotPasswordRequestDto $request): ApiResponse;
    public function ResetPassword(ResetPasswordRequestDto $request): ApiResponse;
}
