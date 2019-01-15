<?php declare(strict_types = 1);
namespace TheSeer\phpDox\DocBlock;

class FactoryException extends \Exception {
    public const InvalidType = 1;

    public const UnknownType = 2;
}
