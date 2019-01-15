<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

class TokenLineEndEvent extends AbstractEvent {
    protected function getEventName() {
        return 'token.line.end';
    }
}
