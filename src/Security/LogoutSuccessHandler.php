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

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

final class LogoutSuccessHandler implements LogoutSuccessHandlerInterface
{
    public function onLogoutSuccess(Request $request): JsonResponse
    {
        $data = [
            'status' => 'ok',
            'error' => '',
        ];

        return new JsonResponse($data);
    }
}
