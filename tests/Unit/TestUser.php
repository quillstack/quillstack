<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Providers\RouteProvider;
use Quillstack\Framework\App;
use Quillstack\Framework\Interfaces\RouteProviderInterface;
use Quillstack\UnitTests\AssertEqual;

class TestUser
{
    public function __construct(private AssertEqual $assertEqual)
    {
        //
    }

    public function routeParameterReachesTheController()
    {
        $_SERVER = [
            'REQUEST_METHOD' => 'GET',
            'HTTP_HOST' => 'localhost',
            'REQUEST_URI' => '/users/42',
            'SERVER_PROTOCOL' => '1.1',
        ];

        $app = new App('', [
            RouteProviderInterface::class => RouteProvider::class,
        ]);

        $this->assertEqual->equal('{"id":"42"}', json_encode($app->run()));
    }
}
