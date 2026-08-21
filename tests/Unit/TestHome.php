<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Providers\RouteProvider;
use Quillstack\Framework\App;
use Quillstack\Framework\Interfaces\RouteProviderInterface;
use Quillstack\UnitTests\AssertEqual;

class TestHome
{
    public function __construct(private AssertEqual $assertEqual)
    {
        //
    }

    public function homeReturnsTheVersion()
    {
        $_SERVER = [
            'REQUEST_METHOD' => 'GET',
            'HTTP_HOST' => 'localhost',
            'REQUEST_URI' => '/',
            'SERVER_PROTOCOL' => '1.1',
        ];

        $app = new App('', [
            RouteProviderInterface::class => RouteProvider::class,
        ]);

        $this->assertEqual->equal(
            '{"app":"The Quillstack Framework","version":"1.0.0"}',
            json_encode($app->run())
        );
    }
}
