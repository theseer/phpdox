<?php
namespace TheSeer\phpDox\Generator {

    class InterfaceCollection extends AbstractCollection {

        public function current() {
            return new InterfaceEntry($this->getCurrentNode());
        }


    }

}
