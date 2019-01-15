<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

/**
 * Class AbstractEvent
 */
abstract class AbstractEvent {
    public function getType() {
        return $this->getEventName();
    }

    abstract protected function getEventName();
}
