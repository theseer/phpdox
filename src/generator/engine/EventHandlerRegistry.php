<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator\Engine;

class EventHandlerRegistry {
    private $events = [
        'phpdox.start' => [],
        'phpdox.end'   => [],

        'phpdox.namespaces.start' => [],
        'phpdox.namespaces.end'   => [],

        'phpdox.classes.start'    => [],
        'phpdox.classes.end'      => [],
        'phpdox.traits.start'     => [],
        'phpdox.traits.end'       => [],
        'phpdox.interfaces.start' => [],
        'phpdox.interfaces.end'   => [],

        'namespace.start'            => [],
        'namespace.classes.start'    => [],
        'namespace.classes.end'      => [],
        'namespace.traits.start'     => [],
        'namespace.traits.end'       => [],
        'namespace.interfaces.start' => [],
        'namespace.interfaces.end'   => [],
        'namespace.end'              => [],

        'class.start'    => [],
        'class.constant' => [],
        'class.member'   => [],
        'class.method'   => [],
        'class.end'      => [],

        'trait.start'    => [],
        'trait.constant' => [],
        'trait.member'   => [],
        'trait.method'   => [],
        'trait.end'      => [],

        'interface.start'    => [],
        'interface.constant' => [],
        'interface.method'   => [],
        'interface.end'      => [],

        'token.file.start' => [],
        'token.line.start' => [],
        'token.token'      => [],
        'token.line.end'   => [],
        'token.file.end'   => []
    ];

    public function addHandler($eventType, $instance, $method): void {
        if (!\method_exists($instance, $method)) {
            throw new EventHandlerRegistryException("Handler '$method' not defined", EventHandlerRegistryException::MethodNotDefined);
        }

        if (!isset($this->events[$eventType])) {
            throw new EventHandlerRegistryException("Event '$eventType' is not defined", EventHandlerRegistryException::EventNotDefined);
        }
        $key = \spl_object_hash($instance) . '::' . $method;

        if (isset($this->events[$eventType][$key])) {
            throw new EventHandlerRegistryException('Handler already registered for this event', EventHandlerRegistryException::AlreadyRegistered);
        }
        $this->events[$eventType][$key] = [$instance, $method];
    }

    public function getHandlersForEvent($eventType) {
        if (!isset($this->events[$eventType])) {
            throw new EventHandlerRegistryException("Event '$eventType' is not defined", EventHandlerRegistryException::EventNotDefined);
        }

        return \array_values($this->events[$eventType]);
    }
}

class EventHandlerRegistryException extends \Exception {
    public const MethodNotDefined = 1;

    public const EventNotDefined = 2;

    public const AlreadyRegistered = 4;
}
