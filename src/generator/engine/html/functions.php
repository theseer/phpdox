<?php
namespace TheSeer\phpDox\Generator\Engine\Html {

    use TheSeer\fDOM\fDOMDocument;
    use TheSeer\fXSL\fXSLTProcessor;

    class Functions {

        protected $projectNode;

        protected $classListDom;
        protected $interfaceListDom;
        protected $traitListDom;
        protected $listXSL;

        protected $dom;
        protected $extension;
        protected $links = array();

        public function __construct(\DOMElement $project, \DOMDocument $cdom, \DOMDocument $idom, \DOMDocument $tdom, fXSLTProcessor $list, $extension = 'xhtml') {
            $this->projectNode = $project;
            $this->classListDom = $cdom;
            $this->interfaceListDom = $idom;
            $this->traitListDom = $tdom;
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
            $full = $nodes[0]->getAttribute('full');
            if (!$full) {
                $full = '';
                if ($nodes[0]->hasAttribute('namespace')) {
                    $full = $nodes[0]->getAttribute('namespace').'\\';
                }
                $full .= $nodes[0]->getAttribute('class');
            }

            if (isset($this->links[$full])) {
                return $this->links[$full];
            }

            $xp = $this->classListDom->getDOMXPath();
            $prepared = $xp->quote($full);
            $node = $this->classListDom->queryOne('//phpdox:class[@full='. $prepared. ']');
            $path = 'classes';
            if (!$node) {
                $node = $this->interfaceListDom->queryOne('//phpdox:interface[@full='. $prepared. ']');
                $path = 'interfaces';
            }
            if (!$node) {
                $text = $this->dom->createTextNode($nodes[0]->getAttribute('class'));
                $span = $this->dom->createElementNS('http://www.w3.org/1999/xhtml', 'span');
                if ($nodes[0]->hasAttribute('namespace')) {
                    $span->setAttribute('title', $full);
                }
                $span->appendChild($text);
                $this->links[$full] = $span;
                return $span;
            }
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

            $xp = $this->classListDom->getDOMXPath();
            $prepared = $xp->quote($full);
            $class = $this->classListDom->queryOne('//phpdox:class[@full='.$prepared.']');
            if (!$class) {
                return $container;
            }

            $this->followInheritence($class, $of);

            foreach($this->classListDom->query('//phpdox:class[phpdox:extends[@full="'.$full.'"]]') as $node) {
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
                $html = $this->listXSL->transformToDoc($this->classListDom)->documentElement;
            }
            return $html;
        }

        public function getTraitList() {
            static $html = null;
            if ($html === null) {
                $html = $this->listXSL->transformToDoc($this->traitListDom)->documentElement;
            }
            return $html;
        }

        public function getInterfaceList() {
            static $html = null;
            if ($html === null) {
                $html = $this->listXSL->transformToDoc($this->interfaceListDom)->documentElement;
            }
            return $html;
        }

        protected function followInheritence($class, $ctx) {
            $node = $this->dom->importNode($class);

            /** @var $extends \DOMElement */
            $extends = $class->queryOne('phpdox:extends');
            if ($extends) {
                $parent = $this->classListDom->queryOne('//phpdox:class[@full="'.$extends->getAttribute('full').'"]');
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
