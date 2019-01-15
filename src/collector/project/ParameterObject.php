<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Collector;

class ParameterObject extends AbstractVariableObject {
    public function setByReference($isRef): void {
        $this->ctx->setAttribute('byreference', $isRef ? 'true' : 'false');
    }
}
