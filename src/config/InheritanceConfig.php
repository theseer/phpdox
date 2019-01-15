<?php declare(strict_types = 1);
namespace TheSeer\phpDox;

use TheSeer\fDOM\fDOMElement;

class InheritanceConfig {
    protected $ctx;

    protected $config;

    public function __construct(CollectorConfig $config, fDOMElement $ctx = null) {
        $this->config = $config;
        $this->ctx    = $ctx;
    }

    public function isPublicOnlyMode() {
        return $this->config->isPublicOnlyMode();
    }

    public function getDependencyDirectories(): array {
        $home    = $this->config->getProjectConfig()->getHomeDirectory();
        $default = new FileInfo($home->getPathname() . '/dependencies/php');
        $list    = [$default];

        if (!$this->ctx) {
            return $list;
        }

        foreach ($this->ctx->query('cfg:dependency') as $dep) {
            if ($dep->hasAttribute('path')) {
                $list[] = new FileInfo($dep->getAttribute('path'));
            }
        }

        return $list;
    }
}
