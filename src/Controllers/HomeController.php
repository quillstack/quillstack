<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Responses\HomeResponse;
use App\Services\VersionService;
use QuillStack\Framework\Interfaces\ControllerInterface;

final class HomeController implements ControllerInterface
{
    /**
     * @var VersionService
     */
    public VersionService $versionService;

    /**
     * @var HomeResponse
     */
    public HomeResponse $response;

    /**
     * {@inheritDoc}
     */
    public function handle(): HomeResponse
    {
        return $this->response->setVersion(
            $this->versionService->getVersion()
        );
    }
}
