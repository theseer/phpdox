<?php
namespace TheSeer\phpDox\Generator {

    class ClassCollection extends AbstractCollection {

        public function current() {
            return new ClassEntry($this->getCurrentNode());
        }


    }

}
