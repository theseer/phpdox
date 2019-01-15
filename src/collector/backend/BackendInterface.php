<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Collector\Backend;

use TheSeer\phpDox\Collector\SourceFile;

interface BackendInterface {
    /**
     * @param bool $publicOnly
     */
    public function parse(SourceFile $sourceFile, $publicOnly): ParseResult;
}
