<?php declare(strict_types = 1);
namespace TheSeer\phpDox;

use TheSeer\phpDox\DocBlock\Factory as DocBlockFactory;

class ParserBootstrapApi {
    private $annotation;

    private $factory;

    public function __construct($annotation, DocBlockFactory $factory) {
        $this->annotation = $annotation;
        $this->factory    = $factory;
    }

    public function implementedByClass($class): void {
        $this->factory->addParserClass($this->annotation, $class);
    }

    public function instantiatedByFactory(FactoryInterface $factory): void {
        $this->factory->addParserFactory($this->annotation, $factory);
    }
}
