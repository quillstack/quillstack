<?php

declare(strict_types=1);

namespace App\Entities;

use Quillstack\Orm\Attributes\Column;
use Quillstack\Orm\Attributes\Id;
use Quillstack\Orm\Attributes\Table;

/**
 * What a client holds instead of a password.
 *
 * The token itself is nowhere here: only its hash, so a database somebody reads holds nothing
 * they could sign in with. Declaring the relation is what indexes `user_id`.
 */
#[Table('api_tokens')]
final class ApiToken
{
    public function __construct(
        #[Id] public ?int $id = null,
        #[Column('user_id')] public ?int $userId = null,
        #[Column(length: 64, unique: true)] public string $hash = '',
        #[Column(length: 0)] public string $roles = ''
    ) {
        //
    }

    /**
     * @return string[]
     */
    public function roles(): array
    {
        return $this->roles === '' ? [] : explode(',', $this->roles);
    }
}
