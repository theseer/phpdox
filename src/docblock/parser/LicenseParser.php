<?php declare(strict_types = 1);
namespace TheSeer\phpDox\DocBlock;

class LicenseParser extends GenericParser {
    public function getObject(array $buffer) {
        $obj = $this->buildObject('generic', $buffer);
        $obj->setName($this->payload);

        return $obj;
    }
}
