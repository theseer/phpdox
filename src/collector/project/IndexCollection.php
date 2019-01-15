<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Collector;

use TheSeer\fDOM\fDOMDocument;
use TheSeer\fDOM\fDOMElement;
use TheSeer\phpDox\FileInfo;

class IndexCollection {
    private $dom;

    /**
     * @var FileInfo
     */
    private $srcDir;

    public function __construct(FileInfo $srcDir) {
        $this->srcDir = $srcDir;
    }

    public function import(fDOMDocument $dom): void {
        $this->dom = $dom;
        $this->dom->registerNamespace('phpdox', 'http://xml.phpdox.net/src');
    }

    /**
     * This method exports all newly registered units into their respective files
     * and updates the collection file accordingly
     */
    public function export(): fDOMDocument {
        if (!$this->dom instanceof fDOMDocument) {
            $this->initDomDocument();
        }

        return $this->dom;
    }

    public function addClass(ClassObject $class): void {
        $this->addUnit($class, 'class');
    }

    public function addInterface(InterfaceObject $interface): void {
        $this->addUnit($interface, 'interface');
    }

    public function addTrait(TraitObject $trait): void {
        $this->addUnit($trait, 'trait');
    }

    public function findUnitNodesBySrcFile(string $path): \DOMNodeList {
        $src = \mb_substr($path, \mb_strlen((string)$this->srcDir) + 1);

        return $this->getRootElement()->query(\sprintf('//*[@src="%s"]', $src));
    }

    /**
     * @param $namespace
     * @param $name
     */
    public function findUnitNodeByName($namespace, $name): ?fDOMElement {
        return $this->getRootElement()->queryOne(
            \sprintf('//phpdox:namespace[@name="%s"]/*[@name="%s"]', $namespace, $name)
        );
    }

    private function getRootElement() {
        if (!$this->dom instanceof fDOMDocument) {
            $this->initDomDocument();
        }

        return $this->dom->documentElement;
    }

    private function initDomDocument(): void {
        $this->dom = new fDOMDocument('1.0', 'UTF-8');
        $this->dom->registerNamespace('phpdox', 'http://xml.phpdox.net/src');
        $index = $this->dom->appendElementNS('http://xml.phpdox.net/src', 'index');
        $index->setAttribute('basedir', $this->srcDir->getRealPath());
    }

    private function addUnit(AbstractUnitObject $unit, $type): void {
        $root = $this->getRootElement();

        if (!$this->findUnitNodeByName($unit->getNamespace(), $unit->getLocalName())) {
            $unitNode = $root->appendElementNS('http://xml.phpdox.net/src', $type);
            $unitNode->setAttribute('name', $unit->getLocalName());

            $src = $unit->getSourceFilename();

            if ($src !== null) {
                $unitNode->setAttribute('src', $src->getRelative($this->srcDir, false));
            }

            $desc = $unit->getCompactDescription();

            if ($desc != '') {
                $unitNode->setAttribute('description', $desc);
            }

            $xpath = 'phpdox:namespace[@name="' . $unit->getNamespace() . '"]';
            $ctx   = $root->queryOne($xpath);

            if (!$ctx) {
                $ctx = $root->appendElementNS('http://xml.phpdox.net/src', 'namespace');
                $ctx->setAttribute('name', $unit->getNamespace());
            }
            $ctx->appendChild($unitNode);
        }
    }
}
