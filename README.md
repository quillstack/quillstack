# Quillstack

[![Tests](https://github.com/quillstack/quillstack/actions/workflows/tests.yml/badge.svg)](https://github.com/quillstack/quillstack/actions/workflows/tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/quillstack/quillstack.svg)](https://packagist.org/packages/quillstack/quillstack)
[![Downloads](https://img.shields.io/packagist/dt/quillstack/quillstack.svg)](https://packagist.org/packages/quillstack/quillstack)
[![PHP Version](https://img.shields.io/packagist/php-v/quillstack/quillstack)](https://packagist.org/packages/quillstack/quillstack)
[![StyleCI](https://github.styleci.io/repos/302760000/shield?branch=main)](https://github.styleci.io/repos/302760000?branch=main)
[![CodeFactor](https://www.codefactor.io/repository/github/quillstack/quillstack/badge)](https://www.codefactor.io/repository/github/quillstack/quillstack)
[![Quality Gate](https://sonarcloud.io/api/project_badges/measure?project=quillstack_quillstack&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=quillstack_quillstack)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=quillstack_quillstack&metric=coverage)](https://sonarcloud.io/summary/new_code?id=quillstack_quillstack)
[![Maintainability](https://sonarcloud.io/api/project_badges/measure?project=quillstack_quillstack&metric=sqale_rating)](https://sonarcloud.io/summary/new_code?id=quillstack_quillstack)
[![Reliability](https://sonarcloud.io/api/project_badges/measure?project=quillstack_quillstack&metric=reliability_rating)](https://sonarcloud.io/summary/new_code?id=quillstack_quillstack)
[![Security](https://sonarcloud.io/api/project_badges/measure?project=quillstack_quillstack&metric=security_rating)](https://sonarcloud.io/summary/new_code?id=quillstack_quillstack)
[![License](https://img.shields.io/packagist/l/quillstack/quillstack)](https://github.com/quillstack/quillstack/blob/main/LICENSE)

A project built on the [Quillstack Framework](https://github.com/quillstack/framework),
a light and simple micro-framework to build APIs.

## Why this exists

A framework is easiest to judge from a project that already runs on it, and hardest to judge
from a list of its parts. This is the running project: one command gives you an API which
answers, with routes, a container, configuration, a database connection and tests already
wired together the way the framework expects.

It is a starting point rather than a dependency. Everything here is yours to change once it is
yours — the layout is a suggestion, and nothing in the framework enforces it.

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
```

`/users/:id` is behind authentication, so asking without a token is refused rather than
answered — see [Authentication](#authentication) for where the token comes from:

```shell
$ curl http://localhost:8000/users/1
{"error":{"status":401,"message":"Not authenticated"}}

$ curl -H "Authorization: Bearer $TOKEN" http://localhost:8000/users/1
{"id":1,"email":"ada@example.com"}
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
    $user = $this->orm->repository(User::class)->find(
        (int) $request->getAttribute('id')
    );

    return $this->response->with($user);
}
```

## What goes over the wire

A response says which object it carries, and the entity says which of its fields may go — beside
the field itself:

```php
#[Table('users')]
final class User
{
    public function __construct(
        #[Id, Exposed] public ?int $id = null,
        #[Column(unique: true), Exposed] public string $email = '',
        #[HasMany(Post::class, 'user_id')] public readonly Related $posts = new Related(),
    ) {
    }
}
```

```json
{"id": 1, "email": "ada@example.com"}
```

The posts are not there: nobody said they may go, so they do not, and loading them is not
started to find that out. `src/Responses/UserResponse.php` is empty apart from the class line,
and there is nothing to keep in step — **a column added tomorrow is not in the API today, and a
field renamed does not quietly stop being sent.** A response written as a list of fields is a
place a field can be forgotten in both directions.

A response serving a particular audience says so, and gets the fields marked for it:

```php
final class AdminUserResponse extends SerializedResponse
{
    protected function groups(): array
    {
        return ['admin'];
    }
}
```

## Authentication

A route says what reaching it requires, and one place enforces it — the controller has nothing
to remember:

```php
$router->get('/users/:id', UserController::class)->name('users.show')->requireAuthentication();
```

`src/Auth/Users.php` says who a token belongs to, which is the one class an application writes
to have authentication at all. What is stored is the hash of the token, so a database somebody
reads holds nothing they could sign in with:

```php
public function findByToken(string $token): ?Identity
{
    $found = $tokens->one($tokens->query()->where('hash', '=', Token::hash($token)));

    return $found === null ? null : new Identity($found->userId, $found->roles());
}
```

```shell
$ curl -i http://localhost:8000/users/1
HTTP/1.1 401 Unauthorized
{"error":{"status":401,"message":"Not authenticated"}}

$ curl -i -H "Authorization: Bearer $TOKEN" http://localhost:8000/users/1
HTTP/1.1 200 OK
{"id":1,"email":"ada@example.com"}
```

Making a token, and keeping only what should be kept:

```php
$token = Token::create();                                    // hand this over, once
$tokens->save(new ApiToken(userId: 1, hash: Token::hash($token)));
```

A guarded route in an application which has said nothing about identities is refused at boot,
before a single request is served — such a route would be open while reading as guarded.

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

`var/queue` is a directory, which is one machine. The moment the application runs on more than
one — behind a load balancer, or with the worker on a box of its own — swap the driver in
`QueueProvider` for the table, which every instance can already reach:

```php
Queue::class => DatabaseQueue::class,
```

There is also `RedisQueue`, for when the queue is busy enough that the messages are worth
keeping out of the database. Start with the table: it needs nothing running that an API does
not already have.

The table is not made by `db:migrate`, which builds what the entities describe and knows
nothing about this one. Create it once, on deploy or by hand:

```php
$container->get(Queue::class)->migrate();
```

Nothing else changes: what pushes messages and what handles them do not know which of the two
they are talking to.

### One thing happening, several things following

Where a queue hands a message to exactly one worker, a topic hands it to everything that
subscribed — a receipt to send, a figure to record, a warehouse to tell, none of the three able
to stop the other two. Subscribers are queues, so `queue:work` handles them as it handles
everything else:

```php
$subscriptions = (new Subscriptions())
    ->subscribe('orders', 'orders.email')
    ->subscribe('orders', 'orders.ledger');

$handlers
    ->handleOn('orders.email', OrderPlaced::class, SendReceiptHandler::class)
    ->handleOn('orders.ledger', OrderPlaced::class, RecordSaleHandler::class);
```

Register both in `QueueProvider`, and publish with
`$topic->publish(new OrderPlaced($id), 'orders')`. See
[quillstack/queue](https://quillstack.org/packages/queue#topics) for what is refused and why.

### Creating, changing and removing

```shell
$ curl -X POST -H "Authorization: Bearer $TOKEN" -H 'Content-Type: application/json' \
       -d '{"email":"ada@example.com"}' http://localhost:8000/users
{"id":1,"email":"ada@example.com"}

$ curl -X DELETE -H "Authorization: Bearer $TOKEN" -i http://localhost:8000/users/1
HTTP/1.1 204 No Content
```

The rules a body has to follow are on the method that handles it, so the same list decides what
is accepted and describes it in the document:

```php
#[Accepts(['email' => ['required', 'email']])]
public function handle(ServerRequestInterface $request): UserResponse
{
    $data = $this->validator->of($request, $this);

    // …
}
```

`DELETE /users/:id` requires the `admin` role and answers `204`, which is what `EmptyResponse`
carries. What each endpoint can refuse with is in the `@throws` above `handle()`.

### Describing the API

```shell
./bin/quill openapi:generate --title="Orders" --server=https://api.example.com --out=public/openapi.json
```

The document is worked out from what is already here — the routes, what a route says it
requires, the rules the validator runs, and what a response says it carries:

```php
#[Serializes(User::class)]
final class UserResponse extends SerializedResponse
{
}
```

Both that and `#[Accepts]` are held to at runtime, so the document describes what the
application does rather than what somebody wrote down once. See
[quillstack/framework](https://quillstack.org/packages/framework#describing-the-api).

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
src/Responses               response classes; one carrying an object needs nothing in it
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

## The rest of Quillstack

Every part of this is a package which stands on its own, and the whole of
[Quillstack](https://github.com/quillstack) can be used a piece at a time.

- [quillstack/framework](https://github.com/quillstack/framework) — what this project is built on
- [quillstack/router](https://github.com/quillstack/router) — the routes above
- [quillstack/di](https://github.com/quillstack/di) — what builds the controllers
- [quillstack/orm](https://github.com/quillstack/orm) — the database layer
- [quillstack/standards](https://github.com/quillstack/standards) — the shape all of them share

Full documentation: https://quillstack.org

## License

MIT. See [LICENSE](LICENSE).
