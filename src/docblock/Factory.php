<?php declare(strict_types = 1);
namespace TheSeer\phpDox\DocBlock;

use TheSeer\fDOM\fDOMDocument;
use TheSeer\phpDox\FactoryInterface;

class Factory {
    private $parserMap = [
        'invalid' => 'TheSeer\\phpDox\\DocBlock\\InvalidParser',
        'generic' => 'TheSeer\\phpDox\\DocBlock\\GenericParser',

        'description' => 'TheSeer\\phpDox\\DocBlock\\DescriptionParser',
        'param'       => 'TheSeer\\phpDox\\DocBlock\\ParamParser',
        'var'         => 'TheSeer\\phpDox\\DocBlock\\VarParser',
        'return'      => 'TheSeer\\phpDox\\DocBlock\\VarParser',
        'throws'      => 'TheSeer\\phpDox\\DocBlock\\VarParser',
        'license'     => 'TheSeer\\phpDox\\DocBlock\\LicenseParser',

        'internal'   => 'TheSeer\\phpDox\\DocBlock\\InternalParser',
        'inheritdoc' => 'TheSeer\\phpDox\\DocBlock\\InheritdocParser'
    ];

    private $elementMap = [
        'inheritdoc' => 'TheSeer\\phpDox\\DocBlock\\InheritdocAttribute',
        'invalid'    => 'TheSeer\\phpDox\\DocBlock\\InvalidElement',
        'generic'    => 'TheSeer\\phpDox\\DocBlock\\GenericElement',
        'var'        => 'TheSeer\\phpDox\\DocBlock\\VarElement'
    ];

    /**
     * Register a parser factory.
     *
     * @param string                                  $annotation identifier of the parser within the registry
     * @param string|\TheSeer\phpDox\FactoryInterface $factory    instance of FactoryInterface to be registered or FQCN
     *                                                            of the object to be created
     *
     * @throws FactoryException in case $annotation is not a string
     */
    public function addParserFactory($annotation, $factory): void {
        $this->verifyType($annotation);
        $this->parserMap[$annotation] = $factory;
    }

    /**
     * Register a parser by its classname.
     *
     * @param string $annotation identifier of the parser within the registry
     * @param string $classname  name of the class representing the parser
     */
    public function addParserClass($annotation, $classname): void {
        $this->verifyType($annotation);
        $this->verifyType($classname);
        $this->parserMap[$annotation] = $classname;
    }

    public function getDocBlock() {
        return new DocBlock();
    }

    public function getInlineProcessor(fDOMDocument $dom) {
        return new InlineProcessor($this, $dom);
    }

    public function getElementInstanceFor($name, $annotation = null) {
        return $this->getInstanceByMap($this->elementMap, $name, $annotation);
    }

    public function getParserInstanceFor($name, $annotation = null) {
        return $this->getInstanceByMap($this->parserMap, $name, $annotation);
    }

    protected function getInstanceByMap($map, $name, $annotation = null) {
        if ($annotation === null) {
            $annotation = $name;
        }

        if (!isset($map[$name])) {
            $name = 'generic';
        }

        if ($map[$name] instanceof FactoryInterface) {
            return $map[$name]->getInstanceFor($name, $this, $annotation);
        }

        return new $map[$name]($this, $annotation);
    }

    /**
     * Verify the type of the given item matches the expected one.
     *
     * @param string $type
     *
     * @throws FactoryException in case the item type and the expected type do not match
     */
    protected function verifyType($item, $type = 'string'): void {
        $match = true;

        switch (\mb_strtolower($type)) {
            case 'string':
                {
                    if (!\is_string($item)) {
                        $match = false;
                    }

                    break;
                }
            default:
                {
                    throw new FactoryException('Unknown type chosen for verification', FactoryException::UnknownType);

                    break;
                }
        }

        if (!$match) {
            throw new FactoryException('Argument must be a string.', FactoryException::InvalidType);
        }
    }
}
