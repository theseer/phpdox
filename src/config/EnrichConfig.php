<?php declare(strict_types = 1);
namespace TheSeer\phpDox;

use TheSeer\fDOM\fDOMElement;

/**
 * @author     Arne Blankerts <arne@blankerts.de>
 * @copyright  Arne Blankerts <arne@blankerts.de>, All rights reserved.
 * @license    BSD License
 */
class EnrichConfig {
    /**
     * @var fDOMElement
     */
    private $ctx;

    /**
     * @var GeneratorConfig
     */
    private $generator;

    public function __construct(GeneratorConfig $generator, fDOMElement $ctx) {
        $this->generator = $generator;
        $this->ctx       = $ctx;
    }

    public function getGeneratorConfig() {
        return $this->generator;
    }

    public function getEnrichNode() {
        return $this->ctx;
    }

    public function getProjectNode() {
        return $this->ctx->parentNode->parentNode;
    }

    public function getType() {
        return $this->ctx->getAttribute('type');
    }

    public function getVersion() {
        return $this->getGeneratorConfig()->getProjectConfig()->getVersion();
    }
}
