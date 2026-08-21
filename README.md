# Quillstack

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

## Layout

```
public/index.php            the entry point
src/Controllers             controllers, one action each
src/Responses               response classes, `send()` returns the payload
src/Providers               routes, commands and services the application brings
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
