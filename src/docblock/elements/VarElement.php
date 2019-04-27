<?php declare(strict_types = 1);
namespace TheSeer\phpDox\DocBlock;

use TheSeer\phpDox\TypeAwareInterface;
use TheSeer\phpDox\TypeAwareTrait;

class VarElement extends GenericElement implements TypeAwareInterface {
    use TypeAwareTrait;

    public const XMLNS = 'http://xml.phpdox.net/src';

    public function asDom(\TheSeer\fDOM\fDOMDocument $ctx) {
        $node = parent::asDom($ctx);
        $type = $node->getAttribute('type');

        if (\strpos($type, '[]')) {
            $type = \mb_substr($type, 0, -2);
            $node->setAttribute('type', 'array');
            $node->setAttribute('of', $type);
        }

        if (!$this->isBuiltInType($type, self::TYPE_PHPDOC|self::TYPE_PHPDOX)) {
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
}
