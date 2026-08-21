<?php

declare(strict_types=1);

namespace App\Providers;

use App\Handlers\SendWelcomeEmailHandler;
use App\Messages\SendWelcomeEmail;
use Quillstack\Framework\Providers\ServiceProvider;
use Quillstack\LocalStorage\LocalStorage;
use Quillstack\Queue\HandlerRegistry;
use Quillstack\Queue\Queue;
use Quillstack\Queue\Queues\FileQueue;

/**
 * Where messages wait, and what handles each of them. Once this is registered the
 * `queue:work` command shows up in `list`.
 */
final class QueueProvider extends ServiceProvider
{
    /**
     * {@inheritDoc}
     */
    public function register(): array
    {
        return [
            Queue::class => new FileQueue(
                new LocalStorage(),
                dirname(__DIR__, 2) . '/var/queue'
            ),
            HandlerRegistry::class => (new HandlerRegistry())
                ->handle(SendWelcomeEmail::class, SendWelcomeEmailHandler::class),
            SendWelcomeEmailHandler::class => [
                'path' => dirname(__DIR__, 2) . '/var/welcome.log',
            ],
        ];
    }
}
