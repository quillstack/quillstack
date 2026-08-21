<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Responses\UserResponse;
use Psr\Http\Message\ServerRequestInterface;
use Quillstack\Framework\Interfaces\ControllerInterface;

final class UserController implements ControllerInterface
{
    public function __construct(private readonly UserResponse $response)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request): UserResponse
    {
        // Route parameters are put on the request as attributes: `/users/:id` gives `id`.
        return $this->response->setId(
            (string) $request->getAttribute('id')
        );
    }
}
