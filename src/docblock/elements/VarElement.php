<?php declare(strict_types = 1);
namespace TheSeer\phpDox\DocBlock;

class VarElement extends GenericElement {
    public const XMLNS = 'http://xml.phpdox.net/src';

    /**
     * @var string[]
     */
    private $types = [
        '', 'null', 'mixed', '{unknown}', 'object', 'array', 'integer', 'int',
        'float', 'string', 'boolean', 'resource'
    ];

    public function asDom(\TheSeer\fDOM\fDOMDocument $ctx) {
        $node = parent::asDom($ctx);
        $type = $node->getAttribute('type');

        $types = explode('|', $type);

        if (count($types) > 1) {
            $node->setAttribute('type', 'mixed');

            foreach ($types as $oneType) {
                $this->typeResolver($node, $oneType);
            }

            return $node;
        }

        if (\strpos($type, '[]')) {
            $type = \mb_substr($type, 0, -2);
            $node->setAttribute('type', 'array');
            $node->setAttribute('of', $type);
        }

        if (!\in_array($type, $this->types)) {
            if (!$node->hasAttribute('of')) {
                $node->setAttribute('type', 'object');
            } else {
                $node->setAttribute('of', 'object');
            }
            $parts     = \explode('\\', $type);
            $local     = \array_pop($parts);
            $namespace = \implode('\\', $parts);

            $class = $node->appendElementNS(self::XMLNS, 'type');
            $class->setAttribute('full', $type);
            $class->setAttribute('namespace', $namespace);
            $class->setAttribute('name', $local);
        }

        return $node;
    }

    protected function typeResolver(\TheSeer\fDOM\fDOMElement $node, string $type) {
        $isArray = false;
        if (substr($type, -2, 2) === '[]') {
            $isArray = true;
            $type = substr($type, 0, -2);
        }
        $nodeType = $node->appendElementNS(self::XMLNS, 'type');
        $nodeType->setAttribute('full', $type);
        $nodeType->setAttribute('array', $isArray?'true':'false');

        if (\in_array($type, $this->types, true)) {
            $nodeType->setAttribute('name', $type);
            $nodeType->setAttribute('namespace', '');
            return;
        }

        $parts     = \explode('\\', $type);
        $local     = \array_pop($parts);
        $namespace = \implode('\\', $parts);

        $nodeType->setAttribute('namespace', $namespace);
        $nodeType->setAttribute('name', $local);
    }
}
