<?php

namespace App\Domain\Services;

use App\Domain\Api\ApiResponse;
use App\Domain\Interfaces\AuthInterface;
use App\Dto\Request\User\ForgotPasswordRequestDto;
use App\Dto\Request\User\RegisterUserRequestDto;
use App\Dto\Request\User\ResetPasswordRequestDto;
use App\Entity\User;
use App\Enums\Code;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthService implements AuthInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly MailerInterface $mailer,
        private readonly string $frontendUrl,
        private readonly string $mailerFrom,
    ) {
    }

    public function Register(RegisterUserRequestDto $request): ApiResponse
    {
        // Format checks (email shape, password length, ...) are covered by
        // RegisterUserRequestDto's Assert constraints, enforced in the controller
        // via ApiValidator before this service is ever called. Only the
        // business rule (uniqueness) is left to check here.
        if (null !== $this->userRepository->findOneByEmail($request->email)) {
            return ApiResponse::error(
                message: 'Un compte existe deja avec cet email',
                code: Code::CONFLICT->value,
            );
        }

        $user = new User($request->email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $request->password));
        $user->setDisplayName($request->displayName);
        $user->setPhone($request->phone);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return ApiResponse::success(
            message: 'Compte cree avec succes',
            code: Code::CREATED->value,
            data: $this->mapUser($user)
        );
    }

    public function Me(User $user): ApiResponse
    {
        return ApiResponse::success(
            message: 'Utilisateur recupere avec succes',
            code: Code::SUCCESS->value,
            data: $this->mapUser($user)
        );
    }

    public function ForgotPassword(ForgotPasswordRequestDto $request): ApiResponse
    {
        $user = $this->userRepository->findOneByEmail($request->email);

        // Whether or not that email is registered, respond the same way:
        // revealing it here would let an attacker enumerate valid accounts.
        if (null !== $user) {
            $token = bin2hex(random_bytes(32));
            $user->setResetToken($token, new \DateTime('+1 hour'));
            $this->entityManager->flush();

            $resetUrl = rtrim($this->frontendUrl, '/').'/reset-password?token='.$token;

            $email = (new Email())
                ->from($this->mailerFrom)
                ->to($user->getEmail())
                ->subject('Reinitialisation de votre mot de passe ASSIME')
                ->text(
                    "Bonjour,\n\n".
                    "Vous avez demande la reinitialisation de votre mot de passe ASSIME.\n".
                    "Cliquez sur le lien suivant (valable 1 heure) pour choisir un nouveau mot de passe :\n\n".
                    $resetUrl."\n\n".
                    "Si vous n'etes pas a l'origine de cette demande, vous pouvez ignorer cet email."
                );

            $this->mailer->send($email);
        }

        return ApiResponse::success(
            message: 'Si un compte existe avec cet email, un lien de reinitialisation vient de lui etre envoye.',
            code: Code::SUCCESS->value,
        );
    }

    public function ResetPassword(ResetPasswordRequestDto $request): ApiResponse
    {
        $user = $this->userRepository->findOneByResetToken($request->token);

        if (null === $user
            || null === $user->getResetTokenExpiresAt()
            || $user->getResetTokenExpiresAt() < new \DateTime()
        ) {
            return ApiResponse::error(
                message: 'Ce lien de reinitialisation est invalide ou a expire',
                code: Code::NOT_VALID->value,
            );
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $request->newPassword));
        $user->setResetToken(null, null);
        $this->entityManager->flush();

        return ApiResponse::success(
            message: 'Mot de passe reinitialise avec succes',
            code: Code::SUCCESS->value,
        );
    }

    /**
     * @return array{id: int|null, email: string, displayName: string|null, phone: string|null, roles: list<string>, createdAt: string}
     */
    private function mapUser(User $user): array
    {
        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'displayName' => $user->getDisplayName(),
            'phone' => $user->getPhone(),
            'roles' => $user->getRoles(),
            'createdAt' => $user->getCreatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }
}
