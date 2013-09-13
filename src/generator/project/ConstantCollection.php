<?php
namespace TheSeer\phpDox\Generator {

    class ConstantCollection extends AbstractCollection {

        public function current() {
            return new ConstantObject($this->getCurrentNode());
        }


    }

}
