<?php
namespace TheSeer\phpDox\Collector {

    class ProjectException extends \Exception {

        const UnitNotFoundInIndex = 1;
        const UnitCouldNotBeSaved = 2;
        const UnexpectedType = 3;
        const ErrorWhileSaving = 4;

    }

}
