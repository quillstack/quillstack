<?php

declare(strict_types=1);

use App\Providers\RouteProvider;
use Quillstack\Framework\App;
use Quillstack\Framework\Interfaces\RouteProviderInterface;

require __DIR__ . '/../vendor/autoload.php';

$app = new App(__DIR__ . '/../.env', [
    RouteProviderInterface::class => RouteProvider::class,
]);

echo json_encode($app->run());
