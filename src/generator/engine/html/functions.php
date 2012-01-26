<?php
namespace TheSeer\phpDox\Engine\Html {

    use TheSeer\fDOM\fDOMDocument;
    use TheSeer\fXSL\fXSLTProcessor;

    class Functions {

        protected $projectNode;

        protected $classListDom;
        protected $interfaceListDom;
        protected $listXSL;

        protected $dom;
        protected $links = array();

        public function __construct(\DOMElement $project, \DOMDocument $cdom, \DOMDocument $idom, fXSLTProcessor $list) {
            $this->projectNode = $project;
            $this->classListDom = $cdom;
            $this->interfaceListDom = $idom;
            $this->listXSL = $list;

            // Helper to create Nodes with
            $this->dom = new fDOMDocument();
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

            $node = $this->classListDom->queryOne('//phpdox:class[@full="'. $full. '"]');
            $path = 'classes';
            if (!$node) {
                $node = $this->interfaceListDom->queryOne('//phpdox:interface[@full="'. $full. '"]');
                $path = 'interfaces';
            }
            if (!$node) {
                $text = $this->dom->createTextNode($nodes[0]->getAttribute('class'));
                if ($nodes[0]->hasAttribute('namespace')) {
                    $span = $this->dom->createElementNS('http://www.w3.org/1999/xhtml','span');
                    $span->setAttribute('title', $full);
                    $span->appendChild($text);
                    $this->links[$full] = $span;
                    return $span;
                } else {
                    $this->links[$full] = $text;
                    return $text;
                }
            }
            $a = $this->dom->createElementNS('http://www.w3.org/1999/xhtml','a');
            $a->setAttribute('href','../'.$path.'/'. $this->classNameToFileName($full,'xhtml'));
            $a->appendChild($this->dom->createTextNode($full));
            $this->links[$full] = $a;
            return $a;
        }

        public function getInheritanceInfo(Array $nodes) {
            if (count($nodes)!=1) {
                return $this->dom->createTextNode('invalid method call');
            }
            $full = $nodes[0]->getAttribute('full');

            $container = $this->dom->createElementNS('http://xml.phpdox.de/src#','extended');
            $by = $this->dom->createElementNS('http://xml.phpdox.de/src#','by');
            $container->appendChild($by);

            $of = $this->dom->createElementNS('http://xml.phpdox.de/src#','of');
            $container->appendChild($of);

            $class = $this->classListDom->queryOne('//phpdox:class[@full="'.$full.'"]');
            if (!$class) {
                return $container;
            }

            $this->followInheritence($class, $of);

            foreach($this->classListDom->query('//phpdox:class[phpdox:extends[@full="'.$full.'"]]') as $node) {
                $by->appendChild($this->dom->importNode($node));
            }

            return $container;

        }


        public function classNameToFileName($class, $ext = 'xml') {
            return str_replace('\\', '_', $class) . '.' . $ext;
        }

        public function getProjectNode() {
            return $this->projectNode;
        }

        public function getClassList() {
            static $html = null;
            if ($html === null) {
                $html = $this->listXSL->transformToDoc($this->classListDom)->documentElement;;
            }
            return $html;
        }

        public function getInterfaceList() {
            static $html = null;
            if ($html === null) {
                $html = $this->listXSL->transformToDoc($this->interfaceListDom)->documentElement;;
            }
            return $html;
        }

        protected function followInheritence($class, $ctx) {
            $node = $this->dom->importNode($class);
            $extends = $class->queryOne('phpdox:extends');
            if ($extends) {
                $parent = $this->classListDom->queryOne('//phpdox:class[@full="'.$extends->getAttribute('full').'"]');
                if ($parent) {
                    $ctx = $this->followInheritence($parent, $ctx);
                }
            }
            $ctx->appendChild($node);
            return $node;
        }
    }
}