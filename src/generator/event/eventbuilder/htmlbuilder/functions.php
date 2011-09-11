<?php
namespace TheSeer\phpDox\htmlBuilder {

    use TheSeer\fDOM\fDOMDocument;

    class Functions {

        protected $classListDom;
        protected $interfaceListDom;

        public function __construct(\DOMDocument $cdom, \DOMDocument $idom) {
            $this->classListDom = $cdom;
            $this->interfaceListDom = $idom;
        }

        public function classLink(\DOMAttribute $classAttribute) {
            throw \Exception('Not implemented yet.');
        }

        public function classNameToFileName($class, $ext = 'xml') {
            return str_replace('\\', '_', $class) . '.' . $ext;
        }

        public function getClassList() {
            return $this->classListDom->documentElement;
        }

        public function getInterfaceList() {
            return $this->interfaceListDom->documentElement;
        }
    }
}