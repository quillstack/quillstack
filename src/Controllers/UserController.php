<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Responses\UserResponse;
use Psr\Http\Message\ServerRequestInterface;
use Quillstack\Framework\Exceptions\Http\NotFoundHttpException;
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
        // An attribute is whatever was put there, so it is worth asking what it is before
        // using it as a string.
        $id = $request->getAttribute('id');

        if (!is_string($id)) {
            throw new NotFoundHttpException('No such user');
        }

        return $this->response->setId($id);
    }
}
