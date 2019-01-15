<?php declare(strict_types = 1);
namespace TheSeer\phpDox\DocBlock;

class LinkParser extends GenericParser {
    public function getObject(array $buffer) {
        $obj   = $this->buildObject('link', $buffer);
        $parts = \preg_split("/[\s,]+/", $this->payload, 2, \PREG_SPLIT_NO_EMPTY);

        if (\count($parts) == 1) {
        }
        $obj->setValue($this->payload);

        return $obj;
    }
}
