<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

class TokenFileEndEvent extends AbstractEvent {
    protected function getEventName() {
        return 'token.line.end';
    }
}
