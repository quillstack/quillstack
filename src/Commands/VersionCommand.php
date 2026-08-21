<?php

declare(strict_types=1);

namespace App\Commands;

use App\Services\VersionService;
use Quillstack\Framework\Console\CommandInterface;
use Quillstack\Framework\Console\Input;
use Quillstack\Output\OutputInterface;

final class VersionCommand implements CommandInterface
{
    public function __construct(private readonly VersionService $versionService)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'app:version';
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(): string
    {
        return 'Shows which version of the application this is';
    }

    /**
     * {@inheritDoc}
     */
    public function run(Input $input, OutputInterface $output): int
    {
        $output->writeln("The Quillstack Framework <green>{$this->versionService->getVersion()}</green>");

        return 0;
    }
}
