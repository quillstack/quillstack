<?php

declare(strict_types=1);

namespace App\Services;

final class VersionService
{
    /**
     * @return string
     */
    public function getVersion(): string
    {
        return '1.0.0';
    }
}
