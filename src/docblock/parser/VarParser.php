<?php declare(strict_types = 1);
namespace TheSeer\phpDox\DocBlock;

class VarParser extends GenericParser {
    public function getObject(array $buffer) {
        $obj = $this->buildObject('var', $buffer);

        if ($this->payload != '') {
            $parts = \preg_split("/[\s,]+/", $this->payload, 2, \PREG_SPLIT_NO_EMPTY);

            if (isset($parts[1])) {
                $obj->setDescription($parts[1]);
            }

            if (\in_array($parts[0], ['self', 'static'])) {
                $obj->setResolution($parts[0]);
            }
            $obj->setType($this->lookupType($parts[0]));
        }

        return $obj;
    }
}
