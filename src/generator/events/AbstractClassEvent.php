<?php
namespace TheSeer\phpDox\Generator {

    abstract class AbstractClassEvent extends AbstractEvent {

        private $class;

        public function __construct($class) {
            $this->class = $class;
        }

        public function getClass() {
            return $this->class;
        }

        public function getNamespace() {

        }
    }

}
