<?php
namespace TheSeer\phpDox\Engine\Html {

    use TheSeer\fDOM\fDOMDocument;

    class Functions {

        protected $projectNode;

        protected $classListDom;
        protected $interfaceListDom;

        public function __construct(\DOMElement $project, \DOMDocument $cdom, \DOMDocument $idom) {
            $this->projectNode = $project;
            $this->classListDom = $cdom;
            $this->interfaceListDom = $idom;
        }

        public function classLink(\DOMAttribute $classAttribute) {
            throw \Exception('Not implemented yet.');
        }

        public function classNameToFileName($class, $ext = 'xml') {
            return str_replace('\\', '_', $class) . '.' . $ext;
        }

        public function getProjectNode() {
            return $this->projectNode;
        }

        public function getClassList() {
            return $this->classListDom->documentElement;
        }

        public function getInterfaceList() {
            return $this->interfaceListDom->documentElement;
        }
    }
}