<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Auth\Users;
use App\Entities\ApiToken;
use App\Entities\User;
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
    private string $token;

    private int $id;

    public function __construct(private AssertEqual $assertEqual)
    {
        // The tables have to be there before anything asks about a token, and a fresh
        // checkout has none — the suite has to stand on its own rather than on whatever the
        // last run left behind.
        $app = $this->app();

        /** @var Migrator $migrator */
        $migrator = $app->container->get(Migrator::class);
        $migrator->migrate([ApiToken::class, User::class]);

        /** @var Orm $orm */
        $orm = $app->container->get(Orm::class);
        $this->token = Token::create();

        $orm->repository(ApiToken::class)->save(
            new ApiToken(userId: 1, hash: Token::hash($this->token))
        );

        // Asked for once per test, so the row may be there already from the last one — and
        // the email is unique. The database decides the id, so the tests ask what it decided
        // rather than telling it.
        $users = $orm->repository(User::class);
        $ada = $users->one($users->query()->where('email', '=', 'ada@example.com'))
            ?? $users->save(new User(email: 'ada@example.com'));

        $this->id = $ada->id ?? 0;
    }

    private function app(): App
    {
        return new App('', [
            RouteProviderInterface::class => RouteProvider::class,
            IdentityProviderInterface::class => Users::class,
            ServiceProviderRegistryInterface::class => ServiceProviderRegistry::class,
        ]);
    }

    private function ask(?string $token = null, ?string $path = null): ResponseInterface
    {
        $_SERVER = [
            'REQUEST_METHOD' => 'GET',
            'HTTP_HOST' => 'localhost',
            'REQUEST_URI' => $path ?? "/users/{$this->id}",
            'SERVER_PROTOCOL' => '1.1',
        ];

        if ($token !== null) {
            $_SERVER['HTTP_AUTHORIZATION'] = "Bearer {$token}";
        }

        return $this->app()->run();
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

    public function withATokenTheUserComesBack()
    {
        $response = $this->ask($this->token);

        $this->assertEqual->equal(200, $response->getStatusCode());
        $this->assertEqual->equal(
            '{"id":' . $this->id . ',"email":"ada@example.com"}',
            json_encode($response)
        );
    }

    /**
     * Nothing here lists the fields: the entity says which of them may go, so a column added
     * beside them is not in the API the day it is added.
     */
    public function andCarriesOnlyWhatTheEntityExposes()
    {
        /** @var array<string, mixed> $sent */
        $sent = json_decode((string) json_encode($this->ask($this->token)), true);

        $this->assertEqual->equal(['id', 'email'], array_keys($sent));
    }

    public function aUserNobodyHasIsNotFound()
    {
        $this->assertEqual->equal(
            404,
            $this->ask($this->token, '/users/999999')->getStatusCode()
        );
    }
}
