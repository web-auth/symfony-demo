<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2019 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace App\Controller;

use App\Entity\User;
use App\Repository\PublicKeyCredentialSourceRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Webauthn\Bundle\Security\Authentication\Token\WebauthnToken;
use Webauthn\PublicKeyCredentialSource;

final class ProfileController
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;
    /**
     * @var PublicKeyCredentialSourceRepository
     */
    private $keyCredentialSourceRepository;

    public function __construct(TokenStorageInterface $tokenStorage, PublicKeyCredentialSourceRepository $keyCredentialSourceRepository)
    {
        $this->tokenStorage = $tokenStorage;
        $this->keyCredentialSourceRepository = $keyCredentialSourceRepository;
    }

    public function __invoke(): JsonResponse
    {
        $token = $this->tokenStorage->getToken();
        if (!$token instanceof WebauthnToken) {
            return new JsonResponse([], JsonResponse::HTTP_UNAUTHORIZED);
        }
        $user = $token->getUser();
        if (!$user instanceof User) {
            return new JsonResponse([], JsonResponse::HTTP_UNAUTHORIZED);
        }
        $credentials = $this->keyCredentialSourceRepository->findAllForUserEntity($user);
        $credentials = array_map(static function (PublicKeyCredentialSource $source) {
            $data = $source->jsonSerialize();
            $data['aaguid'] = $source->getAaguid()->toString();

            return $data;
        }, $credentials);

        return new JsonResponse([
            'isUserPresent' => $token->isUserPresent(),
            'isUserVerified' => $token->isUserVerified(),
            'userForAuthentication' => $token->getCredentials(),
            'user' => $user,
            'created_at' => $user->getCreatedAt()->format('c'),
            'last_login_at' => $user->getLastLoginAt() ? $user->getLastLoginAt()->format('c') : null,
            'credentials' => $credentials,
        ]);
    }
}
