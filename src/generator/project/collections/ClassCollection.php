<?php
namespace TheSeer\phpDox\Generator {

    class ClassCollection extends AbstractCollection {

        /**
         * @return ClassEntry
         */
        public function current() {
            return new ClassEntry($this->getCurrentNode());
        }


    }

}
