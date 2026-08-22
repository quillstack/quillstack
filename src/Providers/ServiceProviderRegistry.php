<?php

declare(strict_types=1);

namespace App\Providers;

use Quillstack\Framework\Providers\ServiceProviderRegistryInterface;

/**
 * The providers of this application, in the order they register.
 */
final class ServiceProviderRegistry implements ServiceProviderRegistryInterface
{
    /**
     * {@inheritDoc}
     */
    public function getProviders(): array
    {
        return [
            DatabaseProvider::class,
            QueueProvider::class,
        ];
    }
}
