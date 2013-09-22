<?php
namespace TheSeer\phpDox\Generator {

    class TraitCollection extends AbstractCollection {

        public function current() {
            return new TraitEntry($this->getCurrentNode());
        }


    }

}
