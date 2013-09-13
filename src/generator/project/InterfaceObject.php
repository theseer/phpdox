<?php
namespace TheSeer\phpDox\Generator {

    use TheSeer\fDOM\fDOMDocument;

    class InterfaceObject {

        /**
         * @var fDOMDocument
         */
        private $dom;

        /**
         * @param fDOMDocument $dom
         */
        public function __construct(fDOMDocument $dom) {
            $this->dom = $dom;
        }

        /**
         * @return fDOMDocument
         */
        public function asDom() {
            return $this->dom;
        }

        /**
         * @return string
         */
        public function getSourceFile() {
            $file = $this->dom->queryOne('//phpdox:file');
            if (!$file) {
                return '';
            }
            return $file->getAttribute('realpath');
        }

        /**
         * @return string
         */
        public function getFullName() {
            return $this->dom->queryOne('//phpdox:class')->getAttribute('full');
        }

        /**
         * @return ConstantCollection
         */
        public function getConstants() {
            return new ConstantCollection($this->dom->query('phpdox:constant'));
        }

        /**
         * @return MethodCollection
         */
        public function getMethods() {
            return new MethodCollection($this->dom->query('phpdox:method'));
        }

    }

}
