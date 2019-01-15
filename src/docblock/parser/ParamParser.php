<?php declare(strict_types = 1);
namespace TheSeer\phpDox\DocBlock;

class ParamParser extends GenericParser {
    public function getObject(array $buffer) {
        $obj = $this->buildObject('var', $buffer);

        $param = \preg_split("/[\s,]+/", $this->payload, 3, \PREG_SPLIT_NO_EMPTY);

        switch (\count($param)) {
            case 3:
                {
                    if ($param[0][0] == '$' || $param[1][0] == '$') {
                        $obj->setDescription($param[2]);
                    // no break!
                    } else {
                        $obj->setDescription($param[1] . ' ' . $param[2]);
                        $obj->setType($this->lookupType($param[0]));

                        break;
                    }
                }
            case 2:
                {
                    if ($param[0][0] == '$') {
                        $obj->setVariable($param[0]);
                        $obj->setType($this->lookupType($param[1]));
                    } else {
                        $obj->setType($this->lookupType($param[0]));
                        $obj->setVariable($param[1]);
                    }

                    break;
                }
            case 1:
                {
                    if ($param[0][0] == '$') {
                        $obj->setVariable($param[0]);
                    } else {
                        $obj->setType($this->lookupType($param[0]));
                    }

                    break;
                }
        }

        return $obj;
    }
}
