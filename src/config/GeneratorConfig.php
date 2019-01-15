<?php declare(strict_types = 1);
namespace TheSeer\phpDox;

use TheSeer\fDOM\fDOMElement;

class GeneratorConfig {
    /**
     * @var array
     */
    private $builds;

    /**
     * @var array
     */
    private $enrichers;

    /**
     * @var fDOMElement
     */
    private $ctx;

    /**
     * @var ProjectConfig
     */
    private $project;

    public function __construct(ProjectConfig $project, fDOMElement $ctx) {
        $this->project = $project;
        $this->ctx     = $ctx;
    }

    public function getProjectConfig() {
        return $this->project;
    }

    public function getActiveBuilds() {
        if (!\is_array($this->builds)) {
            $this->builds = [];

            foreach ($this->ctx->query('cfg:build[@engine and (not(@enabled) or @enabled="true")]') as $ctx) {
                $this->builds[] = new BuildConfig($this, $ctx);
            }
        }

        return $this->builds;
    }

    public function getRequiredEngines() {
        $engines = [];

        foreach ($this->getActiveBuilds() as $build) {
            $engines[] = $build->getEngine();
        }

        return \array_unique($engines);
    }

    public function getRequiredEnrichers() {
        $enrichers = [];

        foreach ($this->getActiveEnrichSources() as $source) {
            $enrichers[] = $source->getType();
        }

        return \array_unique($enrichers);
    }

    public function getActiveEnrichSources() {
        if (!\is_array($this->enrichers)) {
            $this->enrichers = [];

            foreach ($this->ctx->query('cfg:enrich/cfg:source[@type and (not(@enabled) or @enabled="true")]') as $ctx) {
                $this->enrichers[$ctx->getAttribute('type')] = new EnrichConfig($this, $ctx);
            }

            if (!isset($this->enrichers['build'])) {
                $ctx = $this->ctx->ownerDocument->createElementNS('http://xml.phpdox.net/config', 'source');
                $ctx->setAttribute('type', 'build');
                $this->enrichers['build'] = new EnrichConfig($this, $ctx);
            }
        }

        return $this->enrichers;
    }
}
