<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator\Enricher;

class GitEnricherException extends EnricherException {
    public const ExecDisabled = 1;

    public const FetchingHistoryFailed = 2;

    public const GitVersionTooOld = 3;
}
