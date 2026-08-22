<?php

declare(strict_types=1);

namespace App\Entities;

use Quillstack\Orm\Attributes\BelongsTo;
use Quillstack\Orm\Attributes\Column;
use Quillstack\Orm\Attributes\Id;
use Quillstack\Orm\Attributes\Table;
use Quillstack\Orm\Reference;

/**
 * Declaring the relation is what puts an index and a foreign key on `user_id`. Nobody writes
 * either of them.
 */
#[Table('posts')]
final class Post
{
    /**
     * @param Reference<User> $user
     */
    public function __construct(
        #[Id] public ?int $id = null,
        #[Column('user_id')] public ?int $userId = null,
        #[Column] public string $title = '',
        #[BelongsTo(User::class, 'user_id')] public readonly Reference $user = new Reference()
    ) {
        //
    }
}
