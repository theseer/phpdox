<?php
namespace TheSeer\phpDox {

    class ConfigLoaderException extends \Exception {

        const NotFound = 1;
        const ParseError = 2;
        const NeitherCandidateExists = 3;
        const WrongNamespace = 4;
        const WrongType = 5;
    }

}
