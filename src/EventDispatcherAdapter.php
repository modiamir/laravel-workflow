<?php
/**
 * User: amir
 * Date: 5/30/19
 * Time: 2:55 PM
 */

namespace Modiamir;


use Illuminate\Events\Dispatcher;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;

class EventDispatcherAdapter extends EventDispatcher
{
    /**
     * @var Dispatcher
     */
    private $dispatcher;

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Dispatches an event to all registered listeners.
     *
     * @param string $eventName The name of the event to dispatch. The name of
     *                              the event is the name of the method that is
     *                              invoked on listeners.
     * @param Event|null $event The event to pass to the event handlers/listeners
     *                              If not supplied, an empty Event instance is created
     *
     * @return Event
     */
    public function dispatch($eventName, Event $event = null)
    {
        parent::dispatch($eventName, $event);
        $this->dispatcher->dispatch($eventName, [$event]);

        return $event;
    }
}
