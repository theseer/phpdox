<?php declare(strict_types = 1);
namespace TheSeer\phpDox\DocBlock;

class InternalParser extends GenericParser {
    public function getObject(array $buffer) {
        $obj = $this->buildObject('generic', $buffer);
        $obj->setBody($this->payload);

        return $obj;
    }
}
