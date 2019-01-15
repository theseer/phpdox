<?php declare(strict_types = 1);
namespace TheSeer\phpDox;

use TheSeer\fDOM\fDOMElement;

class CollectorConfig {
    protected $ctx;

    protected $project;

    public function __construct(ProjectConfig $project, fDOMElement $ctx) {
        $this->project = $project;
        $this->ctx     = $ctx;
    }

    public function getProjectConfig() {
        return $this->project;
    }

    public function getBackend() {
        if ($this->ctx->hasAttribute('backend')) {
            return $this->ctx->getAttribute('backend', 'parser');
        }

        return 'parser';
    }

    public function getWorkDirectory(): FileInfo {
        return $this->project->getWorkDirectory();
    }

    public function getSourceDirectory(): FileInfo {
        return $this->project->getSourceDirectory();
    }

    public function getFileEncoding(): string {
        return $this->ctx->getAttribute('encoding', 'auto');
    }

    public function isPublicOnlyMode() {
        if ($this->ctx->hasAttribute('publiconly')) {
            return $this->ctx->getAttribute('publiconly', 'false') === 'true';
        }

        return $this->project->isPublicOnlyMode();
    }

    public function getIncludeMasks() {
        return $this->getMasks('include') ?: '*.php';
    }

    public function getExcludeMasks() {
        return $this->getMasks('exclude');
    }

    public function doResolveInheritance() {
        $inNode = $this->ctx->queryOne('cfg:inheritance');

        if (!$inNode) {
            return true;
        }

        return $inNode->getAttribute('resolve', 'true') == 'true';
    }

    public function getInheritanceConfig() {
        return new InheritanceConfig($this, $this->ctx->queryOne('cfg:inheritance'));
    }

    protected function getMasks($nodename) {
        $list = [];

        foreach ($this->ctx->query('cfg:' . $nodename) as $node) {
            $list[] = $node->getAttribute('mask');
        }

        return $list;
    }
}
