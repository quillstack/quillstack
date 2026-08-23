<?php

declare(strict_types=1);

namespace App\Entities;

use Quillstack\Orm\Attributes\Column;
use Quillstack\Orm\Attributes\HasMany;
use Quillstack\Orm\Attributes\Id;
use Quillstack\Orm\Attributes\Table;
use Quillstack\Orm\Related;
use Quillstack\Serializer\Attributes\Exposed;

#[Table('users')]
final class User
{
    /**
     * @param Related<Post> $posts
     */
    public function __construct(
        #[Id, Exposed] public ?int $id = null,
        #[Column(unique: true), Exposed] public string $email = '',
        #[HasMany(Post::class, 'user_id')] public readonly Related $posts = new Related()
    ) {
        //
    }
}
