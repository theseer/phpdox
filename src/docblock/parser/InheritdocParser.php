<?php declare(strict_types = 1);
namespace TheSeer\phpDox\DocBlock;

class InheritdocParser extends GenericParser {
    public function getObject(array $buffer) {
        return $this->buildObject('inheritdoc', $buffer);
    }
}
