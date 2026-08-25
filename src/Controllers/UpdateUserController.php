<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Entities\User;
use App\Responses\UserResponse;
use Psr\Http\Message\ServerRequestInterface;
use Quillstack\Framework\Exceptions\Http\NotFoundHttpException;
use Quillstack\Framework\Interfaces\ControllerInterface;
use Quillstack\Framework\Validation\Attributes\Accepts;
use Quillstack\Framework\Validation\Validator;
use Quillstack\Orm\Orm;

final class UpdateUserController implements ControllerInterface
{
    public function __construct(
        private readonly UserResponse $response,
        private readonly Validator $validator,
        private readonly Orm $orm
    ) {
        //
    }

    /**
     * Changes a user.
     *
     * @throws NotFoundHttpException
     *
     * {@inheritDoc}
     */
    #[Accepts([
        'email' => ['required', 'email'],
    ])]
    public function handle(ServerRequestInterface $request): UserResponse
    {
        $data = $this->validator->of($request, $this);
        $user = $this->find($request);

        $user->email = is_string($data['email'] ?? null) ? $data['email'] : '';
        $this->orm->repository(User::class)->save($user);

        return $this->response->with($user);
    }

    private function find(ServerRequestInterface $request): User
    {
        $id = $request->getAttribute('id');

        if (!is_string($id) || !ctype_digit($id)) {
            throw new NotFoundHttpException('No such user');
        }

        $user = $this->orm->repository(User::class)->find((int) $id);

        if ($user === null) {
            throw new NotFoundHttpException('No such user');
        }

        return $user;
    }
}
