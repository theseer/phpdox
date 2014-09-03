<?php
namespace TheSeer\phpDox\Generator\Engine {

    class EventHandlerRegistry {

        private $events = array(
            'phpdox.start' => array(),
            'phpdox.end' => array(),

            'phpdox.namespaces.start' => array(),
            'phpdox.namespaces.end' => array(),

            'phpdox.classes.start' => array(),
            'phpdox.classes.end' => array(),
            'phpdox.traits.start' => array(),
            'phpdox.traits.end' => array(),
            'phpdox.interfaces.start' => array(),
            'phpdox.interfaces.end' => array(),

            'namespace.start' => array(),
            'namespace.classes.start' => array(),
            'namespace.classes.end' => array(),
            'namespace.traits.start' => array(),
            'namespace.traits.end' => array(),
            'namespace.interfaces.start' => array(),
            'namespace.interfaces.end' => array(),
            'namespace.end' => array(),

            'class.start' => array(),
            'class.constant' => array(),
            'class.member' => array(),
            'class.method' => array(),
            'class.end' => array(),

            'trait.start' => array(),
            'trait.constant' => array(),
            'trait.member' => array(),
            'trait.method' => array(),
            'trait.end' => array(),

            'interface.start' => array(),
            'interface.constant' => array(),
            'interface.method' => array(),
            'interface.end' => array(),

            'token.file.start' => array(),
            'token.line.start' => array(),
            'token.token' => array(),
            'token.line.end' => array(),
            'token.file.end' => array()
        );

        public function addHandler($eventType, $instance, $method) {
            if (!method_exists($instance, $method)) {
                throw new EventHandlerRegistryException("Handler '$method' not defined", EventHandlerRegistryException::MethodNotDefined);
            }
            if (!isset($this->events[$eventType])) {
                throw new EventHandlerRegistryException("Event '$eventType' is not defined", EventHandlerRegistryException::EventNotDefined);
            }
            $key = spl_object_hash($instance) . '::' . $method;
            if (isset($this->events[$eventType][$key])) {
                throw new EventHandlerRegistryException("Handler already registered for this event", EventHandlerRegistryException::AlreadyRegistered);
            }
            $this->events[$eventType][$key] = array($instance, $method);
        }

        public function getHandlersForEvent($eventType) {
            if (!isset($this->events[$eventType])) {
                throw new EventHandlerRegistryException("Event '$eventType' is not defined", EventHandlerRegistryException::EventNotDefined);
            }
            return array_values($this->events[$eventType]);
        }
    }

    class EventHandlerRegistryException extends \Exception {

        const MethodNotDefined  = 1;
        const EventNotDefined   = 2;
        const AlreadyRegistered = 4;
    }

}
