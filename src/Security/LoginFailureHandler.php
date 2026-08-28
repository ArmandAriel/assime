<?php

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationFailureHandler as LexikAuthenticationFailureHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

/**
 * Lexik's default failure handler maps every AuthenticationException to 401
 * (it only respects the exception's ->getCode(), which login-throttling
 * never sets), so a throttled login attempt would otherwise come back as a
 * plain 401 "Invalid credentials" instead of a 429 the client could use to
 * know to back off. This wraps Lexik's handler and just corrects the status
 * code for that one case, keeping its message/translation logic untouched.
 */
final readonly class LoginFailureHandler implements AuthenticationFailureHandlerInterface
{
    public function __construct(
        private LexikAuthenticationFailureHandler $defaultHandler,
    ) {
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $response = $this->defaultHandler->onAuthenticationFailure($request, $exception);

        if ($exception instanceof TooManyLoginAttemptsAuthenticationException) {
            $response->setStatusCode(Response::HTTP_TOO_MANY_REQUESTS);

            $content = json_decode((string) $response->getContent(), true) ?? [];
            $content['code'] = Response::HTTP_TOO_MANY_REQUESTS;
            $response->setContent((string) json_encode($content));
        }

        return $response;
    }
}
