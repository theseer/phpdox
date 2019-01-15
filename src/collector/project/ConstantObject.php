<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Collector;

use TheSeer\fDOM\fDOMElement;
use TheSeer\phpDox\DocBlock\DocBlock;

class ConstantObject {
    protected $ctx;

    public function __construct(fDOMElement $ctx) {
        $this->ctx = $ctx;
        $this->setType('{unknown}');
    }

    public function export() {
        return $this->ctx;
    }

    public function setName($name): void {
        $this->ctx->setAttribute('name', $name);
    }

    public function setValue($value): void {
        $this->ctx->setAttribute('value', $value);
    }

    public function setType($type): void {
        $this->ctx->setAttribute('type', $type);
    }

    public function setConstantReference($const): void {
        $this->ctx->setAttribute('constant', $const);
    }

    public function setDocBlock(DocBlock $docblock): void {
        $docNode = $docblock->asDom($this->ctx->ownerDocument);

        if ($this->ctx->hasChildNodes()) {
            $this->ctx->insertBefore($docblock, $this->ctx->firstChild);

            return;
        }
        $this->ctx->appendChild($docNode);
    }
}
