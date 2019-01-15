<?php declare(strict_types = 1);
namespace TheSeer\phpDox;

use TheSeer\phpDox\Collector\Backend\Factory as BackendFactory;

class BackendBootstrapApi {
    protected $name;

    protected $factory;

    public function __construct($name, BackendFactory $factory) {
        $this->name    = $name;
        $this->factory = $factory;
    }

    public function implementedByClass($class) {
        $this->factory->addBackendClass($this->name, $class);

        return $this;
    }

    public function instantiatedByFactory(FactoryInterface $factory) {
        $this->factory->addBackendFactory($this->name, $factory);

        return $this;
    }
}
