<?php

namespace App\Controller;

use App\Domain\Api\ApiResponse;
use App\Domain\Api\ApiValidator;
use App\Domain\Interfaces\AuthInterface;
use App\Dto\Request\User\ForgotPasswordRequestDto;
use App\Dto\Request\User\RegisterUserRequestDto;
use App\Dto\Request\User\ResetPasswordRequestDto;
use App\Entity\User;
use App\Enums\Code;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class AuthController extends AbstractController
{
    public function __construct(
        private readonly AuthInterface $auth,
        private readonly ApiValidator $apiValidator,
        #[Autowire(service: 'limiter.register_ip')]
        private readonly RateLimiterFactory $registerIpLimiter,
        #[Autowire(service: 'limiter.forgot_password_ip')]
        private readonly RateLimiterFactory $forgotPasswordIpLimiter,
    ) {
    }

    private function tooManyRequestsResponse(string $message, RateLimit $limit): JsonResponse
    {
        $response = ApiResponse::error(message: $message, code: Code::TOO_MANY_REQUESTS->value);

        return $this->json(
            $response->toArray(),
            $response->code,
            ['Retry-After' => (string) $limit->getRetryAfter()->getTimestamp()]
        );
    }

    #[OA\Post(
        path: '/api/auth/register',
        operationId: 'RegisterUser',
        summary: 'Create a new user account',
        tags: ['Auth']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'email', type: 'string'),
                new OA\Property(property: 'password', type: 'string'),
                new OA\Property(property: 'displayName', type: 'string'),
                new OA\Property(property: 'phone', type: 'string'),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'User registered',
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
    #[Route('/api/auth/register', name: 'app_auth_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $limit = $this->registerIpLimiter->create($request->getClientIp())->consume();
        if (!$limit->isAccepted()) {
            return $this->tooManyRequestsResponse('Trop de tentatives de creation de compte. Reessayez plus tard.', $limit);
        }

        $payload = json_decode($request->getContent(), true);

        $dto = new RegisterUserRequestDto(
            email: (string) ($payload['email'] ?? ''),
            password: (string) ($payload['password'] ?? ''),
            displayName: isset($payload['displayName']) ? (string) $payload['displayName'] : null,
            phone: isset($payload['phone']) ? (string) $payload['phone'] : null,
        );

        if ($validationError = $this->apiValidator->validate($dto)) {
            return $this->json($validationError->toArray(), $validationError->code);
        }

        $result = $this->auth->Register($dto);

        return $this->json($result->toArray(), $result->code);
    }

    #[OA\Post(
        path: '/api/auth/login',
        operationId: 'LoginUser',
        summary: 'Authenticate and receive a JWT token',
        tags: ['Auth']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'email', type: 'string'),
                new OA\Property(property: 'password', type: 'string'),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns a JWT token',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'token', type: 'string'),
            ],
            type: 'object'
        )
    )]
    #[Route('/api/auth/login', name: 'app_auth_login', methods: ['POST'])]
    public function login(): never
    {
        // Intercepted by the "login" firewall's json_login listener before reaching here.
        throw new \LogicException('This route is handled by the security firewall, not by this controller.');
    }

    #[OA\Get(
        path: '/api/auth/me',
        operationId: 'GetCurrentUser',
        summary: 'Get the currently authenticated user',
        tags: ['Auth']
    )]
    #[OA\Response(
        response: 200,
        description: 'Current user',
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
    #[Route('/api/auth/me', name: 'app_auth_me', methods: ['GET'])]
    public function me(#[CurrentUser] User $user): JsonResponse
    {
        $result = $this->auth->Me($user);

        return $this->json($result->toArray(), $result->code);
    }

    #[OA\Post(
        path: '/api/auth/forgot-password',
        operationId: 'ForgotPassword',
        summary: 'Request a password reset email',
        tags: ['Auth']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [new OA\Property(property: 'email', type: 'string')]
        )
    )]
    #[OA\Response(response: 200, description: 'Reset email sent, if that account exists')]
    #[Route('/api/auth/forgot-password', name: 'app_auth_forgot_password', methods: ['POST'])]
    public function forgotPassword(Request $request): JsonResponse
    {
        $limit = $this->forgotPasswordIpLimiter->create($request->getClientIp())->consume();
        if (!$limit->isAccepted()) {
            return $this->tooManyRequestsResponse('Trop de demandes. Reessayez plus tard.', $limit);
        }

        $payload = json_decode($request->getContent(), true);

        $dto = new ForgotPasswordRequestDto(
            email: (string) ($payload['email'] ?? ''),
        );

        if ($validationError = $this->apiValidator->validate($dto)) {
            return $this->json($validationError->toArray(), $validationError->code);
        }

        $result = $this->auth->ForgotPassword($dto);

        return $this->json($result->toArray(), $result->code);
    }

    #[OA\Post(
        path: '/api/auth/reset-password',
        operationId: 'ResetPassword',
        summary: 'Reset the password using the token received by email',
        tags: ['Auth']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'token', type: 'string'),
                new OA\Property(property: 'newPassword', type: 'string'),
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Password reset')]
    #[Route('/api/auth/reset-password', name: 'app_auth_reset_password', methods: ['POST'])]
    public function resetPassword(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        $dto = new ResetPasswordRequestDto(
            token: (string) ($payload['token'] ?? ''),
            newPassword: (string) ($payload['newPassword'] ?? ''),
        );

        if ($validationError = $this->apiValidator->validate($dto)) {
            return $this->json($validationError->toArray(), $validationError->code);
        }

        $result = $this->auth->ResetPassword($dto);

        return $this->json($result->toArray(), $result->code);
    }
}
