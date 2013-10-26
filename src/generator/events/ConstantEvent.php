<?php
namespace TheSeer\phpDox\Generator {

    abstract class ConstantEvent extends AbstractEvent {

        private $constant;

        public function __construct(ConstantObject $constant) {
            $this->constant = $constant;
        }

        public function getConstant() {
            return $this->constant;
        }

    }

}
