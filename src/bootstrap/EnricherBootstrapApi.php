<?php declare(strict_types = 1);
namespace TheSeer\phpDox;

use TheSeer\phpDox\Generator\Enricher\Factory as EnricherFactory;

class EnricherBootstrapApi {
    private $name;

    private $factory;

    public function __construct($name, EnricherFactory $factory) {
        $this->name    = $name;
        $this->factory = $factory;
    }

    public function implementedByClass($class) {
        $this->factory->addEnricherClass($this->name, $class);

        return $this;
    }

    public function instantiatedByFactory(FactoryInterface $factory) {
        $this->factory->addEnricherFactory($this->name, $factory);

        return $this;
    }

    public function withConfigClass($class) {
        $this->factory->setConfigClass($this->name, $class);

        return $this;
    }
}
