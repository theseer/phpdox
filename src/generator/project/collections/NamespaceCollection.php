<?php
namespace TheSeer\phpDox\Generator {

    class NamespaceCollection extends AbstractCollection {

        /**
         * @return NamespaceEntry
         */
        public function current() {
            return new NamespaceEntry($this->getCurrentNode());
        }


    }

}
