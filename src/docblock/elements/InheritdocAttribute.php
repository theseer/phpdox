<?php declare(strict_types = 1);
namespace TheSeer\phpDox\DocBlock;

class InheritdocAttribute extends GenericElement {
    public function asDom(\TheSeer\fDOM\fDOMDocument $ctx) {
        $node = $ctx->createAttribute('inherit');
        $node->appendChild($ctx->createTextNode('true'));

        return $node;
    }
}
