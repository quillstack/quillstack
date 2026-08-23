<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Auth\Users;
use App\Entities\ApiToken;
use App\Providers\RouteProvider;
use App\Providers\ServiceProviderRegistry;
use Psr\Http\Message\ResponseInterface;
use Quillstack\Auth\IdentityProviderInterface;
use Quillstack\Auth\Token;
use Quillstack\Framework\App;
use Quillstack\Framework\Interfaces\RouteProviderInterface;
use Quillstack\Framework\Providers\ServiceProviderRegistryInterface;
use Quillstack\Orm\Migration\Migrator;
use Quillstack\Orm\Orm;
use Quillstack\UnitTests\AssertEqual;

class TestUser
{
    public function __construct(private AssertEqual $assertEqual)
    {
        //
    }

    private function ask(?string $token = null): ResponseInterface
    {
        $_SERVER = [
            'REQUEST_METHOD' => 'GET',
            'HTTP_HOST' => 'localhost',
            'REQUEST_URI' => '/users/42',
            'SERVER_PROTOCOL' => '1.1',
        ];

        if ($token !== null) {
            $_SERVER['HTTP_AUTHORIZATION'] = "Bearer {$token}";
        }

        return (new App('', [
            RouteProviderInterface::class => RouteProvider::class,
            IdentityProviderInterface::class => Users::class,
            ServiceProviderRegistryInterface::class => ServiceProviderRegistry::class,
        ]))->run();
    }

    /**
     * The route says only somebody may reach it, and nothing in the controller had to be
     * written for that to be true.
     */
    public function withoutATokenItIsRefused()
    {
        $response = $this->ask();

        $this->assertEqual->equal(401, $response->getStatusCode());
    }

    /**
     * A token standing for nobody is the same answer, because saying which of the two it was
     * would tell whoever is guessing that they are close.
     */
    public function aTokenNobodyKnowsIsRefusedTheSameWay()
    {
        $this->assertEqual->equal(401, $this->ask('nonsense')->getStatusCode());
    }

    public function withATokenTheRouteParameterReachesTheController()
    {
        // A token is made, and only its hash is kept — which is what the provider looks up.
        $token = Token::create();
        $app = new App('', [
            RouteProviderInterface::class => RouteProvider::class,
            IdentityProviderInterface::class => Users::class,
            ServiceProviderRegistryInterface::class => ServiceProviderRegistry::class,
        ]);

        /** @var Orm $orm */
        $orm = $app->container->get(Orm::class);
        /** @var Migrator $migrator */
        $migrator = $app->container->get(Migrator::class);
        $migrator->migrate([ApiToken::class]);

        $orm->repository(ApiToken::class)->save(
            new ApiToken(userId: 1, hash: Token::hash($token))
        );

        $response = $this->ask($token);

        $this->assertEqual->equal(200, $response->getStatusCode());
        $this->assertEqual->equal('{"id":"42"}', json_encode($response));
    }
}
