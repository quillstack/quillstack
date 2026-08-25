<?php

declare(strict_types=1);

namespace App\Responses;

use App\Entities\User;
use Quillstack\Framework\Http\Responses\Attributes\Serializes;
use Quillstack\Framework\Http\Responses\SerializedResponse;

/**
 * What a user looks like on the wire.
 *
 * There is nothing to write here: the entity says which of its fields may go, next to each
 * field, so adding a column tomorrow does not add it to the API by accident and renaming one
 * does not quietly drop it.
 */
#[Serializes(User::class)]
final class UserResponse extends SerializedResponse
{
}
