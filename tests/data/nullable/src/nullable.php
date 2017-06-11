<?php
namespace some\test;

class NullableReturnType {

    public function floatReturn(): ?float {
        return 1.0;
    }

    public function intReturn(): ?int {
        return 1;
    }

    public function stringReturn(): ?string {
        return 'abc';
    }

    public function callableReturn(): ?Callable  {
        return function () {};
    }

    public function boolReturn(): ?bool {
        return false;
    }

    public function arrayReturn(): ?array {
        return [];
    }

    public function objectReturn(): ?ReturnType {
        return new ReturnType();
    }

}
