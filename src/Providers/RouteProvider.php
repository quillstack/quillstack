<?php

declare(strict_types=1);

namespace App\Providers;

use App\Controllers\HomeController;
use App\Providers\Groups\ApiRouteProvider;
use QuillStack\Framework\Interfaces\RouteProviderInterface;
use QuillStack\Router\Router;

final class RouteProvider implements RouteProviderInterface
{
    public ApiRouteProvider $apiRouteProvider;

    /**
     * {@inheritDoc}
     */
    public function getRoutes(Router &$router): void
    {
        $this->apiRouteProvider->getRoutes($router);

        $router->get('/', HomeController::class);
    }
}
