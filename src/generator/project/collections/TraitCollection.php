<?php
namespace TheSeer\phpDox\Generator {

    class TraitCollection extends AbstractCollection {

        /**
         * @return TraitEntry
         */
        public function current() {
            return new TraitEntry($this->getCurrentNode());
        }


    }

}
