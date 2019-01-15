<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Collector\Backend;

class SourceFileException extends \Exception {
    public const BadEncoding = 1;

    public const InvalidDataBytes = 2;
}
