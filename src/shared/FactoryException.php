<?php
namespace TheSeer\phpDox {

    /**
     *
     */
    class FactoryException extends \Exception {

        /**
         *
         */
        const NoClassDefined = 1;
        /**
         *
         */
        const NotInstantiable = 2;
        /**
         *
         */
        const NoConstructor = 3;
    }

}
