<?php declare(strict_types = 1);
namespace TheSeer\phpDox;

class ConfigException extends \Exception {
    public const InvalidDataStructure = 1;

    public const ProjectNotFound = 2;

    public const NoCollectorSection = 3;

    public const NoGeneratorSection = 4;

    public const OverrideNotAllowed = 5;

    public const PropertyNotFound = 6;
}
