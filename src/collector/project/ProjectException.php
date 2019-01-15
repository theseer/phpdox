<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Collector;

class ProjectException extends \Exception {
    public const UnitNotFoundInIndex = 1;

    public const UnitCouldNotBeSaved = 2;

    public const UnexpectedType = 3;

    public const ErrorWhileSaving = 4;
}
