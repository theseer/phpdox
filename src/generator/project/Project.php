<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

use TheSeer\fDOM\fDOMDocument;
use TheSeer\phpDox\FileInfo;

class Project {
    /**
     * @var string
     */
    private $xmlDir;

    /**
     * @var string
     */
    private $srcDir;

    /**
     * @var fDOMDocument
     */
    private $source;

    /**
     * @var fDOMDocument
     */
    private $index;

    public function __construct(FileInfo $srcDir, FileInfo $xmlDir) {
        $this->xmlDir = $xmlDir;
        $this->srcDir = $srcDir;
        $this->initCollections();
    }

    public function getSourceDir(): FileInfo {
        return $this->srcDir;
    }

    public function getXmlDir(): FileInfo {
        return $this->xmlDir;
    }

    public function getIndex(): Index {
        return new Index($this->index);
    }

    public function getSourceTree(): SourceTree {
        return new SourceTree($this->source);
    }

    public function hasNamespaces(): bool {
        return $this->index->query('count(//phpdox:namespace[not(@name="/")])') > 0;
    }

    public function getNamespaces(): NamespaceCollection {
        return new NamespaceCollection($this->index->query('//phpdox:namespace'));
    }

    /**
     * @param string $namespace
     */
    public function getClasses($namespace = null): ClassCollection {
        $root = ($namespace !== null) ? \sprintf('//phpdox:namespace[@name="%s"]/', $namespace) : '//';

        return new ClassCollection($this->index->query($root . 'phpdox:class'));
    }

    /**
     * @param string $namespace
     */
    public function getTraits($namespace = null): TraitCollection {
        $root = ($namespace !== null) ? \sprintf('//phpdox:namespace[@name="%s"]/', $namespace) : '//';

        return new TraitCollection($this->index->query($root . 'phpdox:trait'));
    }

    /**
     * @param string $namespace
     */
    public function getInterfaces($namespace = null): InterfaceCollection {
        $root = ($namespace !== null) ? \sprintf('//phpdox:namespace[@name="%s"]/', $namespace) : '//';

        return new InterfaceCollection($this->index->query($root . 'phpdox:interface'));
    }

    private function initCollections(): void {
        $this->source = new fDOMDocument();
        $this->source->load($this->xmlDir . '/source.xml');
        $this->source->registerNamespace('phpdox', 'http://xml.phpdox.net/src');

        $this->index = new fDOMDocument();
        $this->index->load($this->xmlDir . '/index.xml');
        $this->index->registerNamespace('phpdox', 'http://xml.phpdox.net/src');
    }
}

class ProjectException extends \Exception {
    public const UnitNotFoundInIndex = 1;
}
