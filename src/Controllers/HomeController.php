<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Responses\HomeResponse;
use App\Services\VersionService;
use Psr\Http\Message\ServerRequestInterface;
use Quillstack\Framework\Interfaces\ControllerInterface;

final class HomeController implements ControllerInterface
{
    public function __construct(
        private readonly HomeResponse $response,
        private readonly VersionService $versionService
    ) {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request): HomeResponse
    {
        return $this->response->setVersion(
            $this->versionService->getVersion()
        );
    }
}
