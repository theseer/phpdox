<?php
namespace TheSeer\phpDox\Generator\Enricher {

    class GitEnricherException extends EnricherException {

        const ExecDisabled = 1;
        const FetchingHistoryFailed = 2;
    }

}
