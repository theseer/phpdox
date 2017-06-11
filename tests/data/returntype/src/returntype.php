<?php
namespace some\test;

class ReturnType {

    public function unspecifiedReturn() {
    }

    public function voidReturn(): void {
    }

    public function floatReturn(): float {
        return 1.0;
    }

    public function intReturn(): int {
        return 1;
    }

    public function stringReturn(): string {
        return 'abc';
    }

    public function callableReturn(): Callable  {
        return function () {};
    }

    public function boolReturn(): bool {
        return false;
    }

    public function arrayReturn(): array {
        return [];
    }

    public function always(): ReturnType {
        return new ReturnType();
    }

}
