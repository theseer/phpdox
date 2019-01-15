<?php declare(strict_types = 1);
namespace TheSeer\phpDox\DocBlock;

class DescriptionParser extends GenericParser {
    public function getObject(array $buffer) {
        $compact = '';

        if (\count($buffer)) {
            do {
                $line = \array_shift($buffer);
                $compact .= ' ' . $line;
            } while ($line != '' && \mb_substr($line, -1) != '.');
        }
        $obj = $this->buildObject('generic', $buffer);
        $obj->setCompact(\trim($compact, " *\t"));

        return $obj;
    }
}
