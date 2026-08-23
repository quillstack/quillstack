<?php

declare(strict_types=1);

namespace App\Providers;

use App\Entities\ApiToken;
use App\Entities\Post;
use App\Entities\User;
use Quillstack\Framework\Database\EntityRegistryInterface;

/**
 * The entities of this application. The schema is worked out from them, so this is the one
 * place saying which classes the database is expected to hold.
 */
final class EntityRegistry implements EntityRegistryInterface
{
    /**
     * {@inheritDoc}
     */
    public function getEntities(): array
    {
        return [
            User::class,
            Post::class,
            ApiToken::class,
        ];
    }
}
