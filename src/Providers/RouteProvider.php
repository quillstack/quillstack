<?php

declare(strict_types=1);

namespace App\Providers;

use App\Controllers\HomeController;
use App\Controllers\UserController;
use Quillstack\Framework\Interfaces\RouteProviderInterface;
use Quillstack\Router\Router;

final class RouteProvider implements RouteProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function setRoutes(Router $router): void
    {
        $router->get('/', HomeController::class)->name('home');
        // Saying it here is what makes it true everywhere: one middleware enforces it, so
        // the controller has nothing to remember.
        $router->get('/users/:id', UserController::class)->name('users.show')->requireAuthentication();
    }
}
