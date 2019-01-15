<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator\Engine;

interface EngineInterface {
    public function registerEventHandlers(EventHandlerRegistry $registry);
}
