<?php declare(strict_types = 1);
namespace TheSeer\phpDox\DocBlock;

class InvalidParser extends GenericParser {
    public function getObject(array $buffer) {
        $obj = $this->buildObject('invalid', $buffer);
        $obj->setValue($this->payload);

        return $obj;
    }
}
