<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Requests\HomeRequest;
use App\Responses\HomeResponse;
use App\Services\VersionService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use QuillStack\Framework\Interfaces\ControllerInterface;

final class HomeController implements ControllerInterface
{
    /**
     * @var VersionService
     */
    public VersionService $versionService;

    /**
     * @var HomeRequest
     */
    public HomeRequest $request;

    /**
     * @var HomeResponse
     */
    public HomeResponse $response;

    /**
     * @var LoggerInterface
     */
    public LoggerInterface $logger;

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->logger->info('Home Controller', [
            'request' => $request,
        ]);

        return $this->response->setVersion(
            $this->versionService->getVersion()
        );
    }
}
