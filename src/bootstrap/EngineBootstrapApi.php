<?php declare(strict_types = 1);
namespace TheSeer\phpDox;

use TheSeer\phpDox\Generator\Engine\Factory as EngineFactory;

class EngineBootstrapApi {
    private $name;

    private $factory;

    public function __construct($name, EngineFactory $factory) {
        $this->name    = $name;
        $this->factory = $factory;
    }

    public function implementedByClass($class) {
        $this->factory->addEngineClass($this->name, $class);

        return $this;
    }

    public function instantiatedByFactory(FactoryInterface $factory) {
        $this->factory->addEngineFactory($this->name, $factory);

        return $this;
    }

    public function withConfigClass($class) {
        $this->factory->setConfigClass($this->name, $class);

        return $this;
    }
}
