<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Collector;

use TheSeer\fDOM\fDOMDocument;
use TheSeer\fDOM\fDOMElement;
use TheSeer\phpDox\FileInfo;

class SourceCollection {
    /**
     * @var FileInfo
     */
    private $srcDir;

    /**
     * @var fDOMElement[]
     */
    private $original = [];

    /**
     * @var fDOMElement[]
     */
    private $collection = [];

    private $workDom;

    public function __construct(FileInfo $srcDir) {
        $this->srcDir  = $srcDir;
        $this->workDom = new fDOMDocument();
        $this->workDom->registerNamespace('phpdox', 'http://xml.phpdox.net/src');
        $this->workDom->appendElementNS('http://xml.phpdox.net/src', 'source');
    }

    public function import(fDOMDocument $dom): void {
        $dom->registerNamespace('phpdox', 'http://xml.phpdox.net/src');
        $dir = $dom->queryOne('/phpdox:source/phpdox:dir');

        if (!$dir) {
            return;
        }
        $this->importDirNode($dir, '');
    }

    public function addFile(SourceFile $file) {
        $path = $file->getRealPath();
        $node = $this->workDom->createElementNS('http://xml.phpdox.net/src', 'file');
        $node->setAttribute('name', \basename($file->getBasename()));
        $node->setAttribute('size', $file->getSize());
        $node->setAttribute('time', \date('c', $file->getMTime()));
        $node->setAttribute('unixtime', $file->getMTime());
        $node->setAttribute('sha1', \sha1_file($file->getPathname()));
        $this->collection[$path] = $node;
        $changed                 = $this->isChanged($path);

        if (!$changed) {
            $node->setAttribute('xml', $this->original[$path]->getAttribute('xml'));
        }

        return $changed;
    }

    public function setTokenFileReference(SourceFile $file, $tokenPath): void {
        $path = $file->getRealPath();

        if (!isset($this->collection[$path])) {
            throw new SourceCollectionException(
                \sprintf('File %s not found in collection', $path),
                SourceCollectionException::SourceNotFound
            );
        }
        $this->collection[$path]->setAttribute('xml', $tokenPath);
    }

    public function removeFile(FileInfo $file): void {
        if (!isset($this->collection[$file->getRealPath()])) {
            throw new SourceCollectionException(
                \sprintf('File %s not found in collection', $file->getRealPath()),
                SourceCollectionException::SourceNotFound
            );
        }
        unset($this->collection[$file->getRealPath()]);
    }

    public function getVanishedFiles() {
        $list = [];

        foreach (\array_keys($this->original) as $path) {
            if (!isset($this->collection[$path])) {
                $list[] = $path;
            }
        }

        return $list;
    }

    public function export($collapse = false) {
        if (\count($this->collection) == 0) {
            return $this->workDom;
        }

        $root = $this->workDom->documentElement;

        while ($root->hasChildNodes()) {
            $root->nodeValue = null;
        }

        foreach ($this->collection as $path => $file) {
            $pathInfo = new FileInfo($path);
            $dirs     = \explode('/', \dirname((string)$pathInfo->getRelative($this->srcDir)));
            $dirs[0]  = $this->srcDir->getRealPath();
            $ctx      = $root;

            foreach ($dirs as $dir) {
                $node = $ctx->queryOne('phpdox:dir[@name="' . $dir . '"]');

                if (!$node) {
                    $node = $ctx->appendElementNS('http://xml.phpdox.net/src', 'dir');
                    $node->setAttribute('name', $dir);
                }
                $ctx = $node;
            }
            $ctx->appendChild($this->workDom->importNode($file, true));
        }

        $this->collection = [];

        if ($collapse) {
            $this->collapseDirectory();
        }

        return $this->workDom;
    }

    private function importDirNode(fDOMElement $dir, $path): void {
        $path .= $dir->getAttribute('name');

        foreach ($dir->query('phpdox:file') as $file) {
            $this->original[$path . '/' . $file->getAttribute('name')] = $file;
        }

        foreach ($dir->query('phpdox:dir') as $child) {
            $this->importDirNode($child, $path . '/');
        }
    }

    private function isChanged($path) {
        if (!isset($this->original[$path])) {
            return true;
        }
        $org = $this->original[$path];
        $new = $this->collection[$path];

        return $org->getAttribute('sha1') != $new->getAttribute('sha1');
    }

    private function collapseDirectory(): void {
        $first = $this->workDom->queryOne('/phpdox:source/phpdox:dir');

        if ($first->query('phpdox:file')->length == 0 &&
            $first->query('phpdox:dir')->length == 1) {
            $dir = $first->queryOne('phpdox:dir');

            foreach ($dir->query('*') as $child) {
                $first->appendChild($child);
            }
            $first->setAttribute('name', $first->getAttribute('name') . '/' . $dir->getAttribute('name'));
            $first->removeChild($dir);
            $this->collapseDirectory();
        }
    }
}
