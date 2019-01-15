<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator\Engine;

class HtmlConfig extends \TheSeer\phpDox\BuildConfig {
    public function getTemplateDirectory() {
        $default = $this->getGeneratorConfig()->getProjectConfig()->getHomeDirectory()->getPathname();
        $default .= '/templates/html';
        $node = $this->ctx->queryOne('cfg:template');

        if (!$node) {
            return $default;
        }

        if ($node->hasAttribute('path')) {
            return $node->getAttribute('path', $default);
        }

        return $node->getAttribute('dir', $default);
    }

    public function getResourceDirectory() {
        $default = $this->getTemplateDirectory() . '/static';
        $node    = $this->ctx->queryOne('cfg:resource');

        if (!$node) {
            return $default;
        }

        return $node->getAttribute('path', $default);
    }

    public function getFileExtension() {
        $res = $this->ctx->queryOne('cfg:file/@extension');

        return $res === null ? 'xhtml' : $res->nodeValue;
    }
}
