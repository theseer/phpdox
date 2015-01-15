<?php
namespace TheSeer\phpDox {

    class ConfigException extends \Exception {

        const ProjectNotFound = 1;
        const NoCollectorSection = 2;
        const NoGeneratorSection = 3;
        const OverrideNotAllowed = 4;
        const PropertyNotFound = 5;

    }

}
