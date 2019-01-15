<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Collector;

class CollectorException extends \TheSeer\phpDox\HasFileInfoException {
    public const ProcessingError = 1;
}
