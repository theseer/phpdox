<?php
namespace TheSeer\phpDox\Generator {

    abstract class MethodEvent extends AbstractEvent {

        private $method;

        public function __construct(MethodObject $method) {
            $this->method = $method;
        }

        public function getMethod() {
            return $this->method;
        }

    }

}
