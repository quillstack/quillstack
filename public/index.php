<?php

use App\Providers\RouteProvider;
use QuillStack\Framework\App;
use QuillStack\Framework\Interfaces\RouteProviderInterface;

require __DIR__ . '/../vendor/autoload.php';

$env = __DIR__ . '/../.env';
$app = new App($env, [
    RouteProviderInterface::class => RouteProvider::class,
]);
$response = $app->run();

echo json_encode($response);
