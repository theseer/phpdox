<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator\Engine;

use TheSeer\phpDox\BuildConfig;
use TheSeer\phpDox\FactoryInterface;

class Factory {
    protected $engines = [];

    protected $configs = [];

    public function addEngineClass($name, $class): void {
        $this->engines[$name] = $class;
    }

    public function addEngineFactory($name, FactoryInterface $factory): void {
        $this->engines[$name] = $factory;
    }

    public function getEngineList() {
        return \array_keys($this->engines);
    }

    public function setConfigClass($name, $class): void {
        $this->configs[$name] = $class;
    }

    public function getInstanceFor(BuildConfig $buildCfg) {
        $name = $buildCfg->getEngine();

        if (!isset($this->engines[$name])) {
            throw new FactoryException("Engine '$name' is not registered.", FactoryException::UnknownEngine);
        }

        if (isset($this->configs[$name])) {
            $cfg = new $this->configs[$name]($buildCfg->getGeneratorConfig(), $buildCfg->getBuildNode());
        } else {
            $cfg = $buildCfg;
        }

        if ($this->engines[$name] instanceof FactoryInterface) {
            return $this->engines[$name]->getInstanceFor($name, $cfg);
        }

        return new $this->engines[$name]($cfg);
    }
}

class FactoryException extends \Exception {
    public const UnknownEngine = 1;
}
