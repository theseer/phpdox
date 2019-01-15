<?php declare(strict_types = 1);
namespace TheSeer\phpDox\DocBlock;

class GenericElementException extends \Exception {
    public const MethodNotDefined = 1;

    public const PropertyNotDefined = 2;
}
