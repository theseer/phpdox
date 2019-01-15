<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

use TheSeer\fDOM\fDOMDocument;
use TheSeer\fDOM\fDOMElement;

abstract class AbstractEntry {
    /**
     * @var fDOMElement
     */
    private $node;

    private $dom = [];

    public function __construct(fDOMElement $node) {
        $this->node = $node;
    }

    protected function getNode() {
        return $this->node;
    }

    protected function loadDocument($dir) {
        $path = $dir . '/' . $this->getNode()->getAttribute('xml');

        if (!isset($this->dom[$path])) {
            $classDom = new fDOMDocument();
            $classDom->load($path);
            $classDom->registerNamespace('phpdox', 'http://xml.phpdox.net/src');
            $this->dom[$path] = $classDom;
        }

        return $this->dom[$path];
    }
}
