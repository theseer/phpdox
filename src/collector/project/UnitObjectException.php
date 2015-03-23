<?php
namespace TheSeer\phpDox\Collector {

    /**
     *
     */
    class UnitObjectException extends \Exception {

        /**
         *
         */
        const InvalidRootname = 1;

        /**
         *
         */
        const NoExtends = 2;

        /**
         *
         */
        const NoImplements = 3;

        /**
         *
         */
        const NoTraitsUsed = 4;

        /**
         *
         */
        const NoSuchMethod = 5;

        /**
         *
         */
        const NoSuchTrait = 6;

        /**
         *
         */
        const NoSuchDependency = 7;

    }

}
