<?php

declare(strict_types=1);

namespace App\Providers;

use App\Controllers\HomeController;
use QuillStack\Framework\RouteProviderInterface;
use QuillStack\Router\Router;

final class RouteProvider implements RouteProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function getRoutes(Router &$router): void
    {
        $router->get('/', HomeController::class);
    }
}
