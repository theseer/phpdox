<?php declare(strict_types = 1);
namespace TheSeer\phpDox;

class FactoryException extends \Exception {
    public const NoClassDefined = 1;

    public const NotInstantiable = 2;

    public const NoConstructor = 3;
}
