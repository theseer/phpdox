<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Collector;

use TheSeer\fDOM\fDOMElement;

/**
 * Class AbstractVariableObject
 */
abstract class AbstractVariableObject {
    public const XMLNS = 'http://xml.phpdox.net/src';

    /**
     * @var \TheSeer\fDOM\fDOMElement
     */
    protected $ctx;

    /**
     * @var array
     */
    private $types = ['{unknown}', 'object', 'array', 'int', 'integer', 'float', 'string', 'bool', 'boolean', 'resource', 'callable'];

    public function __construct(fDOMElement $ctx) {
        $this->ctx = $ctx;
    }

    public function export(): fDOMElement {
        return $this->ctx;
    }

    /**
     * @param $line
     */
    public function setLine($line): void {
        $this->ctx->setAttribute('line', $line);
    }

    public function getLine(): string {
        return $this->ctx->getAttribute('line');
    }

    /**
     * @param $name
     */
    public function setName($name): void {
        $this->ctx->setAttribute('name', $name);
    }

    public function getName(): \DOMAttr {
        return $this->ctx->getAttributeNode('name');
    }

    /**
     * @param $value
     */
    public function setDefault($value): void {
        $this->ctx->setAttribute('default', $value);
    }

    public function setConstant($const): void {
        $this->ctx->setAttribute('constant', $const);
    }

    public function isInternalType($type) {
        return \in_array(\mb_strtolower((string)$type), $this->types);
    }

    /**
     * @param $type
     */
    public function setType($type): void {
        if (!$this->isInternalType($type)) {
            $parts     = \explode('\\', (string)$type);
            $local     = \array_pop($parts);
            $namespace = \implode('\\', $parts);

            $unit = $this->ctx->appendElementNS(self::XMLNS, 'type');
            $unit->setAttribute('full', $type);
            $unit->setAttribute('namespace', $namespace);
            $unit->setAttribute('name', $local);
            $type = 'object';
        }
        $this->ctx->setAttribute('type', $type);
    }

    public function getType(): string {
        return $this->ctx->getAttribute('type');
    }

    public function setNullable($isNullable): void {
        $this->ctx->setAttribute('nullable', $isNullable ? 'true' : 'false');
    }

    protected function addInternalType($type): void {
        $this->types[] = $type;
    }
}
