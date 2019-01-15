<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Collector;

use TheSeer\phpDox\DocBlock\DocBlock;

class MemberObject extends AbstractVariableObject {
    /**
     * @param bool $isStatic
     */
    public function setStatic($isStatic): void {
        $this->ctx->setAttribute('static', $isStatic ? 'true' : 'false');
    }

    /**
     * @param string $visibility
     */
    public function setVisibility($visibility): void {
        if (!\in_array($visibility, ['public', 'private', 'protected'])) {
            throw new MethodObjectException("'$visibility' is not valid'", MethodObjectException::InvalidVisibility);
        }
        $this->ctx->setAttribute('visibility', $visibility);
    }

    public function setDocBlock(DocBlock $docblock): void {
        $docNode = $docblock->asDom($this->ctx->ownerDocument);

        if ($this->ctx->hasChildNodes()) {
            $this->ctx->insertBefore($docNode, $this->ctx->firstChild);

            return;
        }
        $this->ctx->appendChild($docNode);
    }
}
