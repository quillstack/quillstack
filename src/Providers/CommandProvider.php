<?php

declare(strict_types=1);

namespace App\Providers;

use App\Commands\VersionCommand;
use Quillstack\Cli\CommandProviderInterface;

final class CommandProvider implements CommandProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function getCommands(): array
    {
        return [
            VersionCommand::class,
        ];
    }
}
