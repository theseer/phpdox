<?php
namespace TheSeer\phpDox {

    class ApplicationException extends \Exception {

        const InvalidSrcDirectory = 1;
        const UnknownEngine = 2;
        const UnknownEnricher = 3;
        const IndexMissing = 4;
        const SourceMissing = 5;
    }

}
