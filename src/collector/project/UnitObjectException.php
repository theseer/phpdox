<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Collector;

class UnitObjectException extends \Exception {
    public const InvalidRootname = 1;

    public const NoExtends = 2;

    public const NoImplements = 3;

    public const NoTraitsUsed = 4;

    public const NoSuchMethod = 5;

    public const NoSuchTrait = 6;

    public const NoSuchDependency = 7;
}
