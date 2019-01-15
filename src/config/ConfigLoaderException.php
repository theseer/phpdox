<?php declare(strict_types = 1);
namespace TheSeer\phpDox;

class ConfigLoaderException extends \Exception {
    public const NotFound = 1;

    public const ParseError = 2;

    public const NeitherCandidateExists = 3;

    public const WrongNamespace = 4;

    public const WrongType = 5;
}
