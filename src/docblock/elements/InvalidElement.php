<?php declare(strict_types = 1);
namespace TheSeer\phpDox\DocBlock;

class InvalidElement extends GenericElement {
    public function asDom(\TheSeer\fDOM\fDOMDocument $ctx) {
        $node = $ctx->createElementNS('http://xml.phpdox.net/src', 'invalid');
        $node->setAttribute('annotation', $this->name);

        foreach ($this->attributes as $attribute => $value) {
            $node->setAttribute($attribute, $value);
        }

        if ($this->body !== null && $this->body !== '') {
            $node->appendChild($ctx->createTextnode($this->body));
        }

        return $node;
    }
}
