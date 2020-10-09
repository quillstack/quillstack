<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Responses\HomeResponse;
use App\Services\VersionService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use QuillStack\Framework\ControllerInterface;

final class HomeController implements ControllerInterface
{
    /**
     * @var VersionService
     */
    public VersionService $versionService;

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $version = $this->versionService->getVersion();

        return (new HomeResponse())->setVersion($version);
    }
}
