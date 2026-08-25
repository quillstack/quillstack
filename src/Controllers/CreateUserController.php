<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Entities\User;
use App\Responses\UserResponse;
use Psr\Http\Message\ServerRequestInterface;
use Quillstack\Framework\Exceptions\Http\ConflictHttpException;
use Quillstack\Framework\Interfaces\ControllerInterface;
use Quillstack\Framework\Validation\Attributes\Accepts;
use Quillstack\Framework\Validation\Validator;
use Quillstack\Orm\Orm;

final class CreateUserController implements ControllerInterface
{
    public function __construct(
        private readonly UserResponse $response,
        private readonly Validator $validator,
        private readonly Orm $orm
    ) {
        //
    }

    /**
     * Takes a new user and keeps them.
     *
     * The rules are on the method rather than inside it, so the same list decides what is
     * accepted and describes it in the OpenAPI document.
     *
     * @throws ConflictHttpException
     *
     * {@inheritDoc}
     */
    #[Accepts([
        'email' => ['required', 'email'],
    ])]
    public function handle(ServerRequestInterface $request): UserResponse
    {
        $data = $this->validator->of($request, $this);

        $users = $this->orm->repository(User::class);

        if ($users->one($users->query()->where('email', '=', $data['email'])) !== null) {
            throw new ConflictHttpException('That email is already taken');
        }

        $user = new User(email: (string) $data['email']);
        $users->save($user);

        return $this->response->with($user);
    }
}
