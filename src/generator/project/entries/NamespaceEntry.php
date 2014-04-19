<?php
namespace TheSeer\phpDox\Generator {

    class NamespaceEntry extends AbstractEntry {

        public function getName() {
            return $this->getNode()->getAttribute('name');
        }

        public function getClasses() {
            return new ClassCollection($this->getNode()->query('phpdox:class'));
        }

        public function getTraits() {
            return new TraitCollection($this->getNode()->query('phpdox:trait'));
        }

        public function getInterfaces() {
            return new InterfaceCollection($this->getNode()->query('phpdox:interface'));
        }

    }

}
