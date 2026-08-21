<?php

declare(strict_types=1);

namespace App\Messages;

/**
 * A message says what is to be done, and carries nothing but the data needed to do it. It
 * waits in a file until a worker picks it up, so everything on it has to survive being
 * written down and read back.
 */
final class SendWelcomeEmail
{
    public function __construct(public readonly string $email)
    {
        //
    }
}
