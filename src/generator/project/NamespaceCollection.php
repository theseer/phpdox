<?php
namespace TheSeer\phpDox\Generator {

    class NamespaceCollection extends AbstractCollection {
        /**
         * (PHP 5 &gt;= 5.0.0)<br/>
         * Return the current element
         *
         * @link http://php.net/manual/en/iterator.current.php
         * @return mixed Can return any type.
         */
        public function current() {
            return new NamespaceEntry($this->getCurrentNode());
        }


    }

}
