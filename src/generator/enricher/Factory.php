<?php

namespace TheSeer\phpDox\Generator\Enricher {

    use TheSeer\phpDox\EnrichConfig;
    use TheSeer\phpDox\FactoryInterface;

    class Factory {

        /**
         * @var array
         */
        private $enrichers = array();

        /**
         * @var array
         */
        private $configs = array();

        public function addEnricherClass($name, $class) {
            $this->enrichers[$name] = $class;
        }

        public function addEnricherFactory($name, FactoryInterface $factory) {
            $this->enrichers[$name] = $factory;
        }

        public function getEnricherList() {
            return array_keys($this->enrichers);
        }

        public function setConfigClass($name, $class) {
            $this->configs[$name] = $class;
        }

        public function getInstanceFor(EnrichConfig $enrichCfg) {
            $name = $enrichCfg->getType();
            if (!isset($this->enrichers[$name])) {
                throw new FactoryException("Enricher '$name' is not registered.", FactoryException::UnknownEnricher);
            }

            if (isset($this->configs[$name])) {
                $cfg = new $this->configs[$name]($enrichCfg->getGeneratorConfig(), $enrichCfg->getEnrichNode());
            } else {
                $cfg = $enrichCfg;
            }

            if ($this->enrichers[$name] instanceof FactoryInterface) {
                return $this->enrichers[$name]->getInstanceFor($name, $cfg);
            }
            return new $this->enrichers[$name]($cfg);
        }

    }

    class FactoryException extends \Exception {
        const UnknownEnricher = 1;
    }

}
