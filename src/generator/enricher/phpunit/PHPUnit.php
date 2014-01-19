<?php
namespace TheSeer\phpDox\Generator\Enricher {

    use TheSeer\fDOM\fDOMDocument;
    use TheSeer\fDOM\fDOMElement;
    use TheSeer\fDOM\fDOMException;
    use TheSeer\fDOM\XPathQuery;
    use TheSeer\phpDox\Collector\MemberObject;
    use TheSeer\phpDox\Generator\ClassStartEvent;
    use TheSeer\phpDox\Generator\InterfaceStartEvent;
    use TheSeer\phpDox\Generator\TraitStartEvent;

    class PHPUnit extends AbstractEnricher implements ClassEnricherInterface, TraitEnricherInterface {

        const XMLNS = 'http://schema.phpunit.de/coverage/1.0';

        /**
         * @var PHPUnitConfig
         */
        private $config;

        /**
         * @var fDOMDocument
         */
        private $index;

        public function __construct(PHPUnitConfig $config) {
            $this->config = $config;
            $this->index = $this->loadXML('index.xml');
        }

        /**
         * @return string
         */
        public function getName() {
            return 'PHPUnit Coverage XML';
        }

        public function enrichClass(ClassStartEvent $event) {
            $this->enrichByFile($event->getClass()->asDom());
        }

        public function enrichTrait(TraitStartEvent $event) {
            $this->enrichByFile($event->getTrait()->asDom());
        }

        private function enrichByFile(fDOMDocument $dom) {
            $fileNode = $dom->queryOne('//phpdox:file');
            if (!$fileNode) {
                return;
            }
            $paths = explode('/', $fileNode->getAttribute('path'));
            $file = $fileNode->getAttribute('file');

            $paths = array_slice($paths, 1);
            $query = '//pu:project/pu:directory';
            foreach($paths as $path) {
                $query .= sprintf('/pu:directory[@name = "%s"]', $path);
            }
            $query .= sprintf('/pu:file[@name = "%s"]', $file);

            $phpunitFileNode = $this->index->queryOne($query);
            if (!$phpunitFileNode) {
                return;
            }

            $refDom = $this->loadXML($phpunitFileNode->getAttribute('href'));
            $this->processUnit($dom, $refDom);
        }

        private function loadXML($fname) {
            try {
                $fname = $this->config->getCoveragePath() . '/' . $fname;
                if (!file_exists($fname)) {
                    throw new EnricherException(
                        sprintf('PHPLoc xml file "%s" not found.', $fname),
                        EnricherException::LoadError
                    );
                }
                $dom = new fDOMDocument();
                $dom->load($fname);
                $dom->registerNamespace('pu', 'http://schema.phpunit.de/coverage/1.0');
                return $dom;
            } catch (fDOMException $e) {
                throw new EnricherException(
                    'Parsing PHPLoc xml file failed: ' . $e->getMessage(),
                    EnricherException::LoadError
                );
            }
        }

        private function processUnit(fDOMDocument $unit, fDOMDocument $coverage) {
            $enrichment = $this->getEnrichtmentContainer($unit->documentElement, 'phpunit');

            $className = $unit->documentElement->getAttribute('name');
            $classNamespace = $unit->documentElement->getAttribute('namespace');

            $classNode = $coverage->queryOne(
                sprintf('//pu:class[@name = "%s" and pu:namespace[@name = "%s"]]', $className, $classNamespace)
            );
            $coverageTarget = $enrichment->appendElementNS(self::XMLNS, 'coverage');
            foreach(array('executable','executed', 'crap') as $attr) {
                $coverageTarget->appendChild(
                    $coverageTarget->ownerDocument->importNode($classNode->getAttributeNode($attr))
                );
            }

            $methods = $unit->query('/phpdox:*/phpdox:constructor|/phpdox:*/phpdox:destructor|/phpdox:*/phpdox:method');
            $xp = $this->index->getDOMXPath();

            foreach($methods as $method) {
                $start = $method->getAttribute('start');
                $end = $method->getAttribute('end');

                $enrichment = $this->getEnrichtmentContainer($method, 'phpunit');
                $coverageTarget = $enrichment->appendElementNS(self::XMLNS, 'coverage');

                /** @var fDOMElement $coverageMethod */
                $coverageMethod = $coverage->queryOne(
                    sprintf('//pu:method[@start = "%d" and @end = "%d"]', $start, $end)
                );

                if ($coverageMethod != NULL) {
                    foreach(array('executable','executed','coverage', 'crap') as $attr) {
                        $coverageTarget->appendChild(
                            $coverageTarget->ownerDocument->importNode($coverageMethod->getAttributeNode($attr))
                        );
                    }
                }

                $coveredNodes = $coverage->query(
                    sprintf('//pu:coverage/pu:line[@nr >= "%d" and @nr <= "%d"]/pu:covered', $start, $end)
                );

                $seen = array();
                foreach($coveredNodes as $coveredNode) {
                    $by = $coveredNode->getAttribute('by');
                    if (isset($seen[$by])) {
                        continue;
                    }
                    $seen[$by] = true;

                    $name = $xp->prepare(':name', array('name' => $by));
                    $coverageTarget->appendChild(
                        $unit->importNode(
                            $this->index->queryOne(
                                sprintf('//pu:tests/pu:test[@name = %s]', $name)
                            )
                        )
                    );
                }

            }
        }
    }

}
