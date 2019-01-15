<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Collector;

class DependencyException extends \Exception {
    public const UnitNotFound = 1;

    public const InvalidUnitType = 2;
}
