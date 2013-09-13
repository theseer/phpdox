<?php
namespace TheSeer\phpDox\Generator {

    class TraitEntry extends AbstractEntry {

        public function getName() {
            return $this->getNode()->getAttribute('name');
        }

        public function asDom() {
            return $this->getNode();
        }

        public function getTraitObject($dir) {
            return new TraitObject($this->loadDocument($dir));
        }


    }

}
