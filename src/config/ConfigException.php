<?php
namespace TheSeer\phpDox {

    class ConfigException extends \Exception {

        const InvalidDataStructure = 1;
        const ProjectNotFound = 2;
        const NoCollectorSection = 3;
        const NoGeneratorSection = 4;
        const OverrideNotAllowed = 5;
        const PropertyNotFound = 6;

    }

}
