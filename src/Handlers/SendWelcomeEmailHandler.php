<?php

declare(strict_types=1);

namespace App\Handlers;

use App\Messages\SendWelcomeEmail;
use Quillstack\LocalStorage\LocalStorage;
use Quillstack\Queue\Handler;

/**
 * Does the work the message asks for. It is built by the container, so it asks for what it
 * needs the same way a controller does: the storage is built for it, and `path` comes from
 * the configuration QueueProvider registers.
 */
final class SendWelcomeEmailHandler implements Handler
{
    public function __construct(
        private readonly LocalStorage $storage,
        private readonly string $path
    ) {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function handle(object $message): void
    {
        if (!$message instanceof SendWelcomeEmail) {
            return;
        }

        // Sending a real email belongs to whatever sends email; writing a line is enough to
        // show that this ran, and where.
        $this->storage->add($this->path, "Welcome, {$message->email}\n");
    }
}
