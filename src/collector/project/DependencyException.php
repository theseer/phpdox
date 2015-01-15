<?php
namespace TheSeer\phpDox\Collector {

    class DependencyException extends \Exception {

        const UnitNotFound = 1;
        const InvalidUnitType = 2;
    }

}
