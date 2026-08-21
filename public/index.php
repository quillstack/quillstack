<?php

declare(strict_types=1);

use App\Providers\RouteProvider;
use App\Providers\ServiceProviderRegistry;
use Quillstack\Framework\App;
use Quillstack\Framework\Interfaces\RouteProviderInterface;
use Quillstack\Framework\Providers\ServiceProviderRegistryInterface;

require __DIR__ . '/../vendor/autoload.php';

$app = new App(__DIR__ . '/../.env', [
    RouteProviderInterface::class => RouteProvider::class,
    ServiceProviderRegistryInterface::class => ServiceProviderRegistry::class,
]);

echo json_encode($app->run());
