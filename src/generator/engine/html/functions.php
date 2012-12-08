<?php
namespace TheSeer\phpDox\Generator\Engine\Html {

    use TheSeer\fDOM\fDOMDocument;
    use TheSeer\fDOM\fDOMElement;
    use TheSeer\fXSL\fXSLTProcessor;

    class Functions {

        protected $projectNode;

        protected $indexDom;
        protected $listXSL;

        protected $dom;
        protected $extension;
        protected $links = array();

        public function __construct(fDOMElement $project, fDOMDocument $index, fXSLTProcessor $list, $extension = 'xhtml') {
            $this->projectNode = $project;
            $this->indexDom = $index;
            $this->listXSL = $list;
            $this->extension = $extension;

            // Helper to create Nodes with
            $this->dom = new fDOMDocument();
        }

        public function version() {
            return PHPDOX_VERSION;
        }

        public function info() {
            $version = $this->version();
            if ($version == '%development%') {
                $version = '(development snapshot)';
            }
            return "Generated using phpDox " . $version . " - Copyright (C) 2010 - 2012 by Arne Blankerts";
        }

        public function classLink(Array $nodes) {
            if (count($nodes)!=1) {
                return $this->dom->createTextNode('invalid method call');
            }
            $workNode = $nodes[0];
            $full = $workNode->getAttribute('full');
            if (($full != '') && isset($this->links[$full])) {
                return $this->links[$full];
            }

            $xp = $this->indexDom->getDOMXPath();
            $node = $this->indexDom->queryOne(
                sprintf('//phpdox:namespace[@name=%s]/phpdox:*[@name=%s]',
                    $xp->quote($workNode->getAttribute('namespace')),
                    $xp->quote($workNode->getAttribute('class'))
                )
            );

            if (!$node) {
                $text = $this->dom->createTextNode($workNode->getAttribute('class'));
                $span = $this->dom->createElementNS('http://www.w3.org/1999/xhtml', 'span');
                if ($nodes[0]->hasAttribute('namespace')) {
                    $span->setAttribute('title', $full);
                }
                $span->appendChild($text);
                $this->links[$full] = $span;
                return $span;
            }

            $map = array(
                'class' => 'classes',
                'interface' => 'interfaces',
                'trait'  => 'traits'
            );
            $path = $map[$node->localName];

            $a = $this->dom->createElementNS('http://www.w3.org/1999/xhtml', 'a');
            $a->setAttribute('href', '../'.$path.'/'. $this->classNameToFileName($full));
            $a->appendChild($this->dom->createTextNode($full));
            $this->links[$full] = $a;
            return $a;
        }

        public function getInheritanceInfo(Array $nodes) {
            if (count($nodes)!=1) {
                return $this->dom->createTextNode('invalid method call');
            }
            $full = $nodes[0]->getAttribute('full');

            $container = $this->dom->createElementNS('http://xml.phpdox.de/src#', 'extended');
            $by = $this->dom->createElementNS('http://xml.phpdox.de/src#', 'by');
            $container->appendChild($by);

            $of = $this->dom->createElementNS('http://xml.phpdox.de/src#', 'of');
            $container->appendChild($of);

            $xp = $this->indexDom->getDOMXPath();
            $prepared = $xp->quote($full);
            $class = $this->indexDom->queryOne('//phpdox:class[@full='.$prepared.']');
            if (!$class) {
                return $container;
            }

            $this->followInheritence($class, $of);

            foreach($this->indexDom->query('//phpdox:class[phpdox:extends[@full="'.$full.'"]]') as $node) {
                $by->appendChild($this->dom->importNode($node));
            }

            return $container;

        }


        public function classNameToFileName($class) {
            return str_replace('\\', '_', $class) . '.' . $this->extension;
        }

        public function getProjectNode() {
            return $this->projectNode;
        }

        public function getClassList() {
            static $html = null;
            if ($html === null) {
                $html = $this->listXSL->transformToDoc($this->indexDom)->documentElement;
            }
            return $html;
        }

        public function getTraitList() {
            static $html = null;
            if ($html === null) {
                $html = $this->listXSL->transformToDoc($this->indexDom)->documentElement;
            }
            return $html;
        }

        public function getInterfaceList() {
            static $html = null;
            if ($html === null) {
                $html = $this->listXSL->transformToDoc($this->indexDom)->documentElement;
            }
            return $html;
        }

        protected function followInheritence($class, $ctx) {
            $node = $this->dom->importNode($class);

            /** @var $extends \DOMElement */
            $extends = $class->queryOne('phpdox:extends');
            if ($extends) {
                $parent = $this->indexDom->queryOne('//phpdox:class[@full="'.$extends->getAttribute('full').'"]');
                if ($parent) {
                    $ctx = $this->followInheritence($parent, $ctx);
                } else {
                    $ctx = $ctx->appendElementNS('http://xml.phpdox.de/src#', 'class');
                    foreach($extends->attributes as $attr) {
                        $ctx->appendChild($this->dom->importNode($attr));
                    }
                }
            }
            $ctx->appendChild($node);
            return $node;
        }
    }
}
