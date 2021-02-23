<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Collector\Backend;

use TheSeer\phpDox\Collector\SourceFile;

interface BackendInterface {
    public function parse(SourceFile $sourceFile, bool $publicOnly): ParseResult;
}
