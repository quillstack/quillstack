<?php

declare(strict_types=1);

namespace App\Services;

final class VersionService
{
    public function getVersion(): string
    {
        return '1.0.0';
    }
}
