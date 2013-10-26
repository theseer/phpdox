<?php
namespace TheSeer\phpDox\Generator {

    class InlineCommentCollection extends AbstractCollection {

        /**
         * @return MethodObject
         */
        public function current() {
            return new InlineCommentObject($this->getCurrentNode());
        }


    }

}
