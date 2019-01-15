<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Collector\Backend;

class ParseErrorException extends \Exception {
    public const GeneralParseError = 1;

    public const UnexpectedExpr = 2;
}
