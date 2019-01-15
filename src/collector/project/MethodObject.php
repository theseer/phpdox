<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Collector;

use TheSeer\fDOM\fDOMElement;
use TheSeer\phpDox\DocBlock\DocBlock;

class MethodObject {
    public const XMLNS = 'http://xml.phpdox.net/src';

    /**
     * @var \TheSeer\fDOM\fDOMElement
     */
    private $ctx;

    /**
     * @var AbstractUnitObject
     */
    private $unit;

    public function __construct(AbstractUnitObject $unit, fDOMElement $ctx) {
        $this->unit = $unit;
        $this->ctx  = $ctx;
    }

    public function getOwner() {
        return $this->unit;
    }

    public function export() {
        return $this->ctx;
    }

    /**
     * @param string $name
     */
    public function setName($name): void {
        $this->ctx->setAttribute('name', $name);
    }

    public function getName() {
        return $this->ctx->getAttribute('name');
    }

    /**
     * @param int $startLine
     */
    public function setStartLine($startLine): void {
        $this->ctx->setAttribute('start', $startLine);
    }

    /**
     * @param int $endLine
     */
    public function setEndLine($endLine): void {
        $this->ctx->setAttribute('end', $endLine);
    }

    /**
     * @param bool $isFinal
     */
    public function setFinal($isFinal): void {
        $this->ctx->setAttribute('final', $isFinal ? 'true' : 'false');
    }

    /**
     * @param bool $isAbstract
     */
    public function setAbstract($isAbstract): void {
        $this->ctx->setAttribute('abstract', $isAbstract ? 'true' : 'false');
    }

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

    public function hasInheritDoc() {
        return $this->ctx->query('phpdox:docblock[@inherit="true"]')->length > 0;
    }

    public function inhertDocBlock(self $method): void {
        $inherit = $method->export()->queryOne('phpdox:docblock');

        if (!$inherit) { // no docblock, no work ;)
            return;
        }
        $docNode = $this->ctx->queryOne('phpdox:docblock');

        if (!$docNode) {
            $this->setDocBlock(new DocBlock());
            $docNode = $this->ctx->queryOne('phpdox:docblock');
        }

        $container = $docNode->appendElementNS(self::XMLNS, 'inherited');
        $container->setAttribute(
            $method->getOwner()->getType(),
            $method->getOwner()->getName()
        );
        $container->appendChild($this->ctx->ownerDocument->importNode($inherit, true));
    }

    /**
     * @param string $name
     */
    public function setReturnType($name): ReturnTypeObject {
        $returnType = new ReturnTypeObject($this->ctx->appendElementNS(self::XMLNS, 'return'));
        $returnType->setType($name);

        return $returnType;
    }

    /**
     * @param string $name
     */
    public function addParameter($name): ParameterObject {
        $parameter = new ParameterObject($this->ctx->appendElementNS(self::XMLNS, 'parameter'));
        $parameter->setName($name);

        return $parameter;
    }

    public function addInlineComment(InlineComment $InlineComment): void {
        $this->getInlineContainer()->appendChild(
            $InlineComment->asDom($this->ctx->ownerDocument)
        );
    }

    private function getInlineContainer(): fDOMElement {
        $node = $this->ctx->queryOne('phpdox:inline');

        if ($node !== null) {
            return $node;
        }

        return $this->ctx->appendElementNS(self::XMLNS, 'inline');
    }
}
