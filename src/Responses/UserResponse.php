<?php

declare(strict_types=1);

namespace App\Responses;

use Quillstack\Response\Response;

final class UserResponse extends Response
{
    private string $id = '';

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function send(): array
    {
        return [
            'id' => $this->id,
        ];
    }
}
