<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Handlers\SendWelcomeEmailHandler;
use App\Messages\SendWelcomeEmail;
use App\Providers\ServiceProviderRegistry;
use Quillstack\Auth\IdentityProviderInterface;
use Quillstack\Framework\App;
use Quillstack\Framework\Providers\ServiceProviderRegistryInterface;
use Quillstack\Queue\HandlerRegistry;
use Quillstack\Queue\Queue;
use Quillstack\Queue\Queues\ArrayQueue;
use Quillstack\Queue\Worker;
use Quillstack\UnitTests\AssertEqual;

class TestQueue
{
    public function __construct(private AssertEqual $assertEqual)
    {
        //
    }

    public function theHandlerIsTheOneRegisteredForTheMessage()
    {
        $app = new App('', [
            ServiceProviderRegistryInterface::class => ServiceProviderRegistry::class,
        ]);

        /** @var HandlerRegistry $handlers */
        $handlers = $app->container->get(HandlerRegistry::class);

        $this->assertEqual->equal(
            SendWelcomeEmailHandler::class,
            $handlers->handlerFor(new SendWelcomeEmail('ada@example.com'))
        );
    }

    /**
     * The queue a test uses is one held in memory, so nothing is written to disk.
     */
    public function aMessageIsHandledOnce()
    {
        $queue = new ArrayQueue();
        $path = sys_get_temp_dir() . '/quillstack-welcome-test.log';
        @unlink($path);

        $app = new App('', [
            ServiceProviderRegistryInterface::class => ServiceProviderRegistry::class,
            Queue::class => $queue,
            // What the application configures wins over what a provider registers, so the
            // test writes its line somewhere of its own.
            SendWelcomeEmailHandler::class => ['path' => $path],
        ]);

        $queue->push(new SendWelcomeEmail('ada@example.com'));

        /** @var Worker $worker */
        $worker = $app->container->get(Worker::class);

        $this->assertEqual->equal(1, $worker->runAll());
        $this->assertEqual->equal(0, $worker->runAll());
        $this->assertEqual->equal("Welcome, ada@example.com\n", file_get_contents($path));

        @unlink($path);
    }
}
