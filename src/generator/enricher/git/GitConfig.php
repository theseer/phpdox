<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator\Enricher;

use TheSeer\fDOM\fDOMElement;
use TheSeer\phpDox\FileInfo;
use TheSeer\phpDox\GeneratorConfig;

class GitConfig {
    /**
     * @var GeneratorConfig
     */
    private $generator;

    /**
     * @var fDOMElement
     */
    private $context;

    public function __construct(GeneratorConfig $generator, fDOMElement $ctx) {
        $this->context   = $ctx;
        $this->generator = $generator;
    }

    public function getSourceDirectory(): FileInfo {
        return $this->generator->getProjectConfig()->getSourceDirectory();
    }

    public function getGitBinary(): string {
        $git = $this->context->queryOne('cfg:git');

        if (!$git) {
            return 'git';
        }

        return $git->getAttribute('binary', 'git');
    }

    public function doLogProcessing(): bool {
        $history = $this->context->queryOne('cfg:history');

        if (!$history) {
            return true;
        }

        return $history->getAttribute('enabled', 'true') == 'true';
    }

    public function getLogLimit(): int {
        $history = $this->context->queryOne('cfg:history');

        if (!$history) {
            return 100;
        }

        return (int)$history->getAttribute('limit', 100);
    }

    public function getLogfilePath() {
        $history = $this->context->queryOne('cfg:history');

        if (!$history || $history->getAttribute('cache') == '') {
            return $this->generator->getProjectConfig()->getWorkDirectory() . '/gitlog.xml';
        }

        return $history->getAttribute('cache');
    }
}
