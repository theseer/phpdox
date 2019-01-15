<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Collector;

use TheSeer\fDOM\fDOMElement;

class ReturnTypeObject extends AbstractVariableObject {
    public function __construct(fDOMElement $ctx) {
        parent::__construct($ctx);
        $this->addInternalType('void');
    }
}
