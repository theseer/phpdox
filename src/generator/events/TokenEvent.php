<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

class TokenEvent extends AbstractEvent {
    protected function getEventName() {
        return 'token';
    }
}
