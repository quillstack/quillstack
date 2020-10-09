<?php

declare(strict_types=1);

namespace App\Responses;

use QuillStack\Http\Response\AbstractResponse;

final class HomeResponse extends AbstractResponse
{
    /**
     * @var string
     */
    private string $version;

    /**
     * @param string $version
     *
     * @return $this
     */
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
            'app' => 'The Quill Stack Framework',
            'version' => $this->version,
        ];
    }
}
