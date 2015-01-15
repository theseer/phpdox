<?php

namespace TheSeer\phpDox\Collector {

    use TheSeer\fDOM\fDOMDocument;

    class Dependency {

        private $index;
        private $project;
        private $baseDir;

        public function __construct(fDOMDocument $dom, Project $project) {
            $this->index = $dom;
            $this->baseDir = dirname(urldecode($dom->documentURI));
            $this->index->registerNamespace('phpdox', 'http://xml.phpdox.net/src');
            $this->project = $project;
        }

        public function getUnitByName($name) {
            $parts = explode('\\', $name);
            $local = array_pop($parts);
            $namespace = join('\\', $parts);
            $indexNode = $this->index->queryOne(
                    sprintf('//phpdox:namespace[@name="%s"]/*[@name="%s"]', $namespace, $local));

            if (!$indexNode) {
                throw new DependencyException(
                    sprintf("Unit '%s' not found", $name),
                    DependencyException::UnitNotFound
                );
            }

            $dom = new fDOMDocument();
            $dom->load( $this->baseDir . '/' . $indexNode->getAttribute('xml'));

            switch ($indexNode->localName) {
                case 'interface': {
                    $unit = new InterfaceObject();
                    $unit->import($dom);
                    $this->project->addInterface($unit);
                    break;
                }
                case 'trait': {
                    $unit = new TraitObject();
                    $unit->import($dom);
                    $this->project->addTrait($unit);
                    break;
                }
                case 'class': {
                    $unit = new ClassObject();
                    $unit->import($dom);
                    $this->project->addClass($unit);
                    break;
                }
                default: {
                    throw new DependencyException(
                        sprintf("Invalid unit type '%s'", $indexNode->localName),
                        DependencyException::InvalidUnitType
                    );
                }
            }

            return $unit;
        }

    }

}
