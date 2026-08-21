<?php

declare(strict_types=1);

namespace App\Responses;

use Quillstack\Response\Response;

final class HomeResponse extends Response
{
    private string $version = '';

    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function send(): array
    {
        return [
            'app' => 'The Quillstack Framework',
            'version' => $this->version,
        ];
    }
}
