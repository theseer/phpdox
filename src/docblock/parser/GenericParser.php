<?php declare(strict_types = 1);
namespace TheSeer\phpDox\DocBlock;

class GenericParser {
    protected $factory;

    protected $aliasMap;

    protected $name;

    protected $payload;

    private $types = [
        '', 'null', 'mixed', '{unknown}', 'object', 'array', 'integer', 'int',
        'float', 'string', 'boolean', 'resource'
    ];

    public function __construct(Factory $factory, $name) {
        $this->factory = $factory;
        $this->name    = $name;
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
        if ($type === 'self' || $type === 'static') {
            return $this->aliasMap['::unit'];
        }

        // Do not mess with scalar and fixed types
        if (\in_array($type, $this->types)) {
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
