<?php declare(strict_types=1);
namespace some\test;

class foo {

    public static function createFromFoo(): self {
        return new self;
    }

    public static function createFromBar(): ?self {
        return new self;
    }

}
