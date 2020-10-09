<?php

use App\Providers\RouteProvider;
use QuillStack\Framework\App;
use QuillStack\Framework\RouteProviderInterface;

require __DIR__ . '/../vendor/autoload.php';

$app = new App([
    RouteProviderInterface::class => RouteProvider::class,
]);
$response = $app->run();

echo $response;
