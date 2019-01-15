<?php declare(strict_types = 1);
namespace TheSeer\phpDox;

class ApplicationException extends \Exception {
    public const InvalidSrcDirectory = 1;

    public const UnknownEngine = 2;

    public const UnknownEnricher = 3;

    public const IndexMissing = 4;

    public const SourceMissing = 5;
}
