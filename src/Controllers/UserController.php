<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Entities\User;
use App\Responses\UserResponse;
use Psr\Http\Message\ServerRequestInterface;
use Quillstack\Framework\Exceptions\Http\NotFoundHttpException;
use Quillstack\Framework\Interfaces\ControllerInterface;
use Quillstack\Orm\Orm;

final class UserController implements ControllerInterface
{
    public function __construct(
        private readonly UserResponse $response,
        private readonly Orm $orm
    ) {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request): UserResponse
    {
        // Route parameters are put on the request as attributes: `/users/:id` gives `id`.
        // An attribute is whatever was put there, so it is worth asking what it is before
        // using it as a number.
        $id = $request->getAttribute('id');

        if (!is_string($id) || !ctype_digit($id)) {
            throw new NotFoundHttpException('No such user');
        }

        $user = $this->orm->repository(User::class)->find((int) $id);

        if ($user === null) {
            throw new NotFoundHttpException('No such user');
        }

        return $this->response->with($user);
    }
}
