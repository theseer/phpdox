<?php declare(strict_types = 1);
namespace TheSeer\phpDox;

use TheSeer\fDOM\fDOMElement;

/**
 * @author     Arne Blankerts <arne@blankerts.de>
 * @copyright  Arne Blankerts <arne@blankerts.de>, All rights reserved.
 * @license    BSD License
 */
class BuildConfig {
    protected $ctx;

    protected $generator;

    protected $project;

    public function __construct(GeneratorConfig $generator, fDOMElement $ctx) {
        $this->generator = $generator;
        $this->project   = $generator->getProjectConfig();
        $this->ctx       = $ctx;
    }

    public function getGeneratorConfig() {
        return $this->generator;
    }

    public function getBuildNode() {
        return $this->ctx;
    }

    public function getProjectNode() {
        return $this->ctx->parentNode->parentNode;
    }

    public function getEngine() {
        return $this->ctx->getAttribute('engine');
    }

    public function getWorkDirectory() {
        return $this->project->getWorkDirectory();
    }

    public function getOutputDirectory() {
        $path = '';

        if ($this->ctx->parentNode->hasAttribute('output')) {
            $path = $this->ctx->parentNode->getAttribute('output', 'docs');
        }

        if ($this->ctx->hasAttribute('output')) {
            if ($path != '') {
                $path .= '/';
            }
            $path .= $this->ctx->getAttribute('output');
        }

        return new FileInfo($path);
    }

    public function getSourceDirectory() {
        return $this->project->getSourceDirectory();
    }
}
