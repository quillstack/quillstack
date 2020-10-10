<?php

declare(strict_types=1);

namespace App\Providers\Groups;

use App\Controllers\HomeController;
use QuillStack\Framework\Interfaces\RouteProviderInterface;
use QuillStack\Router\Router;

final class ApiRouteProvider implements RouteProviderInterface
{
    public function getRoutes(Router &$router): void
    {
        $router->get('/api', HomeController::class);
    }
}
