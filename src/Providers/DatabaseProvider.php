<?php

declare(strict_types=1);

namespace App\Providers;

use Quillstack\Db\Connection;
use Quillstack\Framework\Providers\ServiceProvider;
use Quillstack\Orm\Migration\Migrator;
use Quillstack\Orm\Orm;

/**
 * Where the database is. Building the connection does not open it, so a request which never
 * asks it anything pays nothing.
 */
final class DatabaseProvider extends ServiceProvider
{
    /**
     * An environment value as a string, or nothing where it is absent or empty.
     */
    private static function text(string $key): ?string
    {
        $value = env($key);

        return is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * {@inheritDoc}
     */
    public function register(): array
    {
        // A key which is present but empty is a key nobody has filled in, so it counts as
        // absent rather than as the empty string — which is not a data source name.
        $dsn = (string) env('DB_DSN');

        if ($dsn === '') {
            $directory = dirname(__DIR__, 2) . '/var';
            is_dir($directory) || mkdir($directory, 0o775, true);
            $dsn = "sqlite:{$directory}/database.sqlite";
        }

        $connection = new Connection($dsn, self::text('DB_USER'), self::text('DB_PASSWORD'));

        return [
            Connection::class => $connection,
            Orm::class => new Orm($connection),
            Migrator::class => new Migrator($connection),
        ];
    }
}
