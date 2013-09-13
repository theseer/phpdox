<?php
namespace TheSeer\phpDox\Generator {

    class InterfaceEntry extends AbstractEntry {

        public function getName() {
            return $this->getNode()->getAttribute('name');
        }

        public function asDom() {
            return $this->getNode();
        }

        public function getInterfaceObject($dir) {
            return new InterfaceObject($this->loadDocument($dir));
        }


    }

}
