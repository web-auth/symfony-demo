<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

use App\Controller\ChangeDisplayNameController;
use App\Controller\LogoutController;
use App\Controller\ProfileController;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use App\Controller\HomepageController;

$routes = new RouteCollection();

// Profile API
$routes->add('api_profile', new Route('/api/profile',
    ['_controller' => ProfileController::class],
    [],[],null,[],['GET']
));

$routes->add('api_change_display_name', new Route('/api/change_display_name',
    ['_controller' => ChangeDisplayNameController::class],
    [],[],null,[],['PUT']
));

// Logout API
$routes->add('api_logout', new Route('/api/logout',
    ['_controller' => LogoutController::class],
    [],[],null,[],['GET']
));

// Home
$routes->add('app_home', new Route('/{reactRouting}',
    [
        '_controller' => [HomepageController::class, 'home'],
        'reactRouting' => null
    ],
    ['reactRouting' => '^(?!_).*$']
));

return $routes;
