<?php declare(strict_types = 1);
namespace TheSeer\phpDox\DocBlock;

use TheSeer\phpDox\TypeInfo;

class GenericParser {

    protected $factory;

    protected $aliasMap;

    protected $name;

    protected $payload;

    /**
     * @var TypeInfo
     */
    protected $typeInfo;

    public function __construct(Factory $factory, $name) {
        $this->factory  = $factory;
        $this->name     = $name;
        $this->typeInfo = new TypeInfo();
    }

    public function setAliasMap(array $map): void {
        $this->aliasMap = $map;
    }

    public function setPayload($payload): void {
        $this->payload = \trim($payload);
    }

    public function getObject(array $buffer) {
        $this->payload .= ' ' . \implode(' ', \array_map('trim', $buffer));
        $obj = $this->buildObject('generic', []);
        $obj->setValue(\trim($this->payload));

        return $obj;
    }

    protected function buildObject($classname, array $buffer) {
        $obj = $this->factory->getElementInstanceFor($classname, $this->name);

        if (\count($buffer)) {
            $obj->setBody(\trim(\implode("\n", $buffer)));
        }

        return $obj;
    }

    protected function lookupType($type) {
        $types = explode('|', $type);

        foreach ($types as &$oneType) {
            $isArray = false;
            if (substr($oneType, -2, 2) === '[]') {
                $isArray = true;
                $oneType = substr($oneType, 0, -2);
            }

            $oneType = $this->lookupOneType($oneType);

            if ($isArray) {
                $oneType .= '[]';
            }
        }

        return implode('|', $types);
    }

    protected function lookupOneType($type) {
        if ($type === 'self' || $type === 'static') {
            return $this->aliasMap['::unit'];
        }

        // Do not mess with scalar and fixed types
        if ($this->typeInfo->isBuiltInType($type)) {
            return $type;
        }

        // absolute definition?
        if ($type[0] == '\\') {
            return $type;
        }

        // alias?
        if (isset($this->aliasMap[$type])) {
            return $this->aliasMap[$type];
        }

        // relative to local namespace?
        if (isset($this->aliasMap['::context'])) {
            return $this->aliasMap['::context'] . '\\' . $type;
        }

        // don't know any better ..
        return $type;
    }
}
