<?php
namespace Tests\SymfonyRollbarBundle\EventListener;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;
use Symfony\Component\HttpKernel\KernelEvents;
use SymfonyRollbarBundle\EventListener\AbstractListener;
use SymfonyRollbarBundle\EventListener\ErrorListener;
use SymfonyRollbarBundle\EventListener\ExceptionListener;

/**
 * Class AbstractListenerTest
 * @package Tests\SymfonyRollbarBundle\EventListener
 */
class AbstractListenerTest extends KernelTestCase
{
    public function setUp()
    {
        parent::setUp();

        static::bootKernel();
    }

    public function testListeners()
    {
        $container = static::$kernel->getContainer();

        /**
         * @var TraceableEventDispatcher $eventDispatcher
         */
        $eventDispatcher = $container->get('event_dispatcher');
        $listeners = $eventDispatcher->getListeners('kernel.exception');

        $expected = [
            \SymfonyRollbarBundle\EventListener\AbstractListener::class,
            \Symfony\Component\HttpKernel\EventListener\ExceptionListener::class,
        ];

        foreach ($listeners as $listener) {
            $ok = $listener[0] instanceof $expected[0] || $listener[0] instanceof $expected[1];
            $this->assertTrue($ok, 'Listeners were not registered');
        }
    }

    /**
     * @return array
     */
    public function generatorGetSubscribedEvents()
    {
        return [
            [ErrorListener::class],
            [ExceptionListener::class],
        ];
    }

    /**
     * @dataProvider generatorGetSubscribedEvents
     *
     * @param string $class
     */
    public function testGetSubscribedEvents($class)
    {
        /**
         * @var AbstractListener $listener
         */
        $container = static::$kernel->getContainer();
        $listener  = new $class($container);

        $expect = [
            KernelEvents::EXCEPTION => ['onKernelException', 1],
        ];
        $list = $listener::getSubscribedEvents();

        $this->assertEquals($expect, $list);
    }

    /**
     * @dataProvider generatorGetSubscribedEvents
     *
     * @param string $class
     */
    public function testGetLogger($class)
    {
        /**
         * @var AbstractListener $listener
         */
        $container = static::$kernel->getContainer();
        $listener  = new $class($container);

        $logger = $listener->getLogger();
        $this->assertTrue($logger instanceof \Monolog\Logger);
    }

    /**
     * @dataProvider generatorGetSubscribedEvents
     *
     * @param string $class
     */
    public function testGetContainer($class)
    {
        /**
         * @var AbstractListener $listener
         */
        $container1 = static::$kernel->getContainer();
        $listener   = new $class($container1);

        $container2 = $listener->getContainer();
        $this->assertTrue($container2 instanceof \Symfony\Component\DependencyInjection\ContainerInterface);
        $this->assertEquals($container1, $container2);
    }

    /**
     * @dataProvider generatorGetSubscribedEvents
     *
     * @param string $class
     */
    public function testGetGenerator($class)
    {
        /**
         * @var AbstractListener $listener
         */
        $container = static::$kernel->getContainer();
        $listener  = new $class($container);

        $generator = $listener->getGenerator();
        $this->assertTrue($generator instanceof \SymfonyRollbarBundle\Payload\Generator);
    }
}
