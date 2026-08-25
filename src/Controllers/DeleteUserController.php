<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Entities\User;
use Psr\Http\Message\ServerRequestInterface;
use Quillstack\Framework\Exceptions\Http\NotFoundHttpException;
use Quillstack\Framework\Http\Responses\EmptyResponse;
use Quillstack\Framework\Interfaces\ControllerInterface;
use Quillstack\Orm\Orm;

final class DeleteUserController implements ControllerInterface
{
    public function __construct(private readonly Orm $orm)
    {
        //
    }

    /**
     * Removes a user.
     *
     * The answer is the status: an `EmptyResponse` carries `204`, and the document says `204`
     * because it read the response rather than assuming every answer is a `200`.
     *
     * @throws NotFoundHttpException
     *
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request): EmptyResponse
    {
        $id = $request->getAttribute('id');

        if (!is_string($id) || !ctype_digit($id)) {
            throw new NotFoundHttpException('No such user');
        }

        $users = $this->orm->repository(User::class);
        $user = $users->find((int) $id);

        if ($user === null) {
            throw new NotFoundHttpException('No such user');
        }

        $users->delete($user);

        return new EmptyResponse();
    }
}
