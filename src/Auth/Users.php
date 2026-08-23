<?php

declare(strict_types=1);

namespace App\Auth;

use App\Entities\ApiToken;
use Quillstack\Auth\Identity;
use Quillstack\Auth\IdentityProviderInterface;
use Quillstack\Auth\Token;
use Quillstack\Orm\Orm;

/**
 * Who a token belongs to.
 *
 * Where the tokens are kept is the application's business, which is why this is the one class
 * an application writes to have authentication at all.
 */
final class Users implements IdentityProviderInterface
{
    public function __construct(private readonly Orm $orm)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function findByToken(string $token): ?Identity
    {
        $tokens = $this->orm->repository(ApiToken::class);

        // What is looked up is the hash, because that is what was stored.
        $found = $tokens->one(
            $tokens->query()->where('hash', '=', Token::hash($token))
        );

        if ($found === null || $found->userId === null) {
            return null;
        }

        return new Identity($found->userId, $found->roles());
    }
}
