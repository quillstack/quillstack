# Quillstack

[![Tests](https://github.com/quillstack/quillstack/actions/workflows/tests.yml/badge.svg)](https://github.com/quillstack/quillstack/actions/workflows/tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/quillstack/quillstack.svg)](https://packagist.org/packages/quillstack/quillstack)
[![Downloads](https://img.shields.io/packagist/dt/quillstack/quillstack.svg)](https://packagist.org/packages/quillstack/quillstack)
[![PHP Version](https://img.shields.io/packagist/php-v/quillstack/quillstack)](https://packagist.org/packages/quillstack/quillstack)
[![StyleCI](https://github.styleci.io/repos/302760000/shield?branch=main)](https://github.styleci.io/repos/302760000?branch=main)
[![License](https://img.shields.io/packagist/l/quillstack/quillstack)](https://github.com/quillstack/quillstack/blob/main/LICENSE)

A project built on the [Quillstack Framework](https://github.com/quillstack/framework),
a light and simple micro-framework to build APIs.

## Requirements

- PHP 8.1 or newer
- the `json` extension
- [Composer](https://getcomposer.org/)

## Getting started

```shell
composer create-project quillstack/quillstack my-api
cd my-api
composer serve
```

`composer create-project` copies `.env.example` to `.env` for you. When you clone this
repository by hand, do it yourself:

```shell
composer install
cp .env.example .env
composer serve
```

The application is then served at http://localhost:8000:

```shell
$ curl http://localhost:8000/
{"app":"The Quillstack Framework","version":"1.0.0"}

$ curl http://localhost:8000/users/42
{"id":"42"}
```

## Routes

Routes live in `src/Providers/RouteProvider.php`:

```php
public function setRoutes(Router $router): void
{
    $router->get('/', HomeController::class)->name('home');
    $router->get('/users/:id', UserController::class)->name('users.show');
}
```

`get()`, `post()`, `put()`, `patch()`, `delete()`, `options()` and `head()` register a single
method, `match(['PUT', 'PATCH'], ...)` registers a few of them, and `any()` registers them all.

A path segment written as `:id` or as `{id}` is a parameter. Matched parameters are put on the
request as attributes, so a controller reads them from the request it is handed:

```php
public function handle(ServerRequestInterface $request): UserResponse
{
    return $this->response->setId(
        (string) $request->getAttribute('id')
    );
}
```

## Queues

Work which does not have to happen while somebody is waiting goes on a queue. A message says
what is to be done, a handler does it:

```php
$queue->push(new SendWelcomeEmail($email));
```

`src/Providers/QueueProvider.php` says where messages wait and what handles each of them.
The example writes them under `var/queue` and handles them by appending to `var/welcome.log`.

```shell
./bin/quill queue:work                    # everything due now, then stop
./bin/quill queue:work emails             # a queue of its own
./bin/quill queue:work --keep-running     # wait for more
```

A message which fails is tried again a few times, waiting longer each time, and then set
aside rather than kept in the way of everything behind it.

## Database

Entities describe the tables, so there are no migration files to write and none to keep in
order. `src/Entities` holds them and `src/Providers/EntityRegistry.php` names them:

```php
#[Table('posts')]
final class Post
{
    /** @param Reference<User> $user */
    public function __construct(
        #[Id] public ?int $id = null,
        #[Column('user_id')] public ?int $userId = null,
        #[Column] public string $title = '',
        #[BelongsTo(User::class, 'user_id')] public readonly Reference $user = new Reference(),
    ) {
    }
}
```

Declaring that relation is what puts an index and a foreign key on `posts.user_id`. Nobody
writes either:

```shell
$ ./bin/quill db:migrate --pretend
  CREATE TABLE "posts" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "user_id" INTEGER NULL,
    "title" TEXT NOT NULL,
    FOREIGN KEY ("user_id") REFERENCES "users" ("id") ON DELETE CASCADE
  )
  CREATE INDEX "posts_user_id_index" ON "posts" ("user_id")
```

Drop `--pretend` to run it. Nothing is ever removed: a column the entities no longer mention
is reported and left alone.

Left unconfigured the database is a SQLite file under `var/`; `DB_DSN` in `.env` points it
anywhere else. Reading is where [quillstack/orm](https://github.com/quillstack/orm) earns its
keep — touching one entity's relation loads it for every entity read beside it, so walking
users, their posts and the comments on those is three queries rather than one per row.

## Layout

```
public/index.php            the entry point
src/Controllers             controllers, one action each
src/Responses               response classes, `send()` returns the payload
src/Providers               routes, commands and services the application brings
src/Entities                the tables, and the relations between them
src/Messages                what goes on a queue
src/Handlers                what handles it
src/Services                your own services
tests/unit.php              the list of test classes to run
```

Controllers, services and responses are resolved by the container. Ask for what you need
through the constructor:

```php
final class HomeController implements ControllerInterface
{
    public function __construct(
        private readonly HomeResponse $response,
        private readonly VersionService $versionService
    ) {
    }
}
```

## Tests

```shell
composer test
```

Static analysis runs at PHPStan's strictest level:

```shell
composer stan
```

Coverage needs phpdbg, which is a separate binary shipped with PHP:

```shell
composer test:coverage
```

## License

MIT. See [LICENSE](LICENSE).
