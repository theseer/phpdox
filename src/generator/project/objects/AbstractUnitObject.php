<?php
namespace TheSeer\phpDox\Generator {

    use TheSeer\fDOM\fDOMDocument;

    class AbstractUnitObject {

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

        public function getInlineComments() {
            return new InlineCommentCollection($this->dom->query('phpdox:inline'));
        }

        /**
         * @return string
         */
        public function getSourceFile() {
            $file = $this->asDom()->queryOne('//phpdox:file');
            if (!$file) {
                return '';
            }
            return $file->getAttribute('realpath');
        }

        /**
         * @return string
         */
        public function getFullName() {
            return $this->dom->documentElement->getAttribute('full');
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
            return new MethodCollection($this->dom->query('phpdox:constructor|phpdox:method|phpdox:destructor'));
        }


    }

}
