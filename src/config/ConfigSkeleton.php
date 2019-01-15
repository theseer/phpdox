<?php declare(strict_types = 1);
namespace TheSeer\phpDox;

use TheSeer\fDOM\fDOMDocument;

class ConfigSkeleton {
    /**
     * @var FileInfo
     */
    private $file;

    public function __construct(FileInfo $file) {
        $this->file = $file;
    }

    public function render(): string {
        return \file_get_contents($this->file->getPathname());
    }

    public function renderStripped(): string {
        $dom                     = new fDOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->loadXML(
            \preg_replace("/\s{2,}/u", ' ', $this->render())
        );

        foreach ($dom->query('//comment()') as $c) {
            $c->parentNode->removeChild($c);
        }
        $dom->formatOutput = true;

        return $dom->saveXML();
    }
}
