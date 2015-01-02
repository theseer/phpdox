<?php
namespace TheSeer\phpDox\Generator\Enricher {

    use TheSeer\fDOM\fDOMDocument;
    use TheSeer\fDOM\fDOMElement;
    use TheSeer\fDOM\fDOMException;
    use TheSeer\phpDox\FileInfo;
    use TheSeer\phpDox\Generator\ClassStartEvent;
    use TheSeer\phpDox\Generator\PHPDoxEndEvent;
    use TheSeer\phpDox\Generator\TokenFileStartEvent;
    use TheSeer\phpDox\Generator\TraitStartEvent;

    class PHPUnit extends AbstractEnricher implements
        EndEnricherInterface, ClassEnricherInterface, TraitEnricherInterface, TokenFileEnricherInterface {

        const XMLNS = 'http://schema.phpunit.de/coverage/1.0';

        /**
         * @var Fileinfo
         */
        private $coveragePath;

        /**
         * @var fDOMDocument
         */
        private $index;


        private $results = array();
        private $coverage = array();

        /**
         * @param PHPUnitConfig $config
         *
         * @throws EnricherException
         */
        public function __construct(PHPUnitConfig $config) {
            $this->coveragePath = $config->getCoveragePath();
            $this->index = $this->loadXML('index.xml');
        }

        /**
         * @return string
         */
        public function getName() {
            return 'PHPUnit Coverage XML';
        }

        public function enrichEnd(PHPDoxEndEvent $event) {
            $index = $event->getIndex()->asDom();
            foreach($this->results as $namespace => $classes) {
                foreach($classes as $class => $results) {
                    $classNode = $index->queryOne(
                        sprintf('//phpdox:namespace[@name = "%s"]/phpdox:class[@name = "%s"]', $namespace, $class)
                    );
                    if (!$classNode) {
                        continue;
                    }
                    $container = $this->getEnrichtmentContainer($classNode, 'phpunit');
                    $resultNode = $container->appendElementNS(self::XMLNS, 'result');
                    foreach($results as $key => $value) {
                        $resultNode->setAttribute(mb_strtolower($key), $value);
                    }
                    $container->appendChild(
                        $container->ownerDocument->importNode($this->coverage[$namespace][$class])
                    );
                }
            }
        }

        public function enrichClass(ClassStartEvent $event) {
            $this->enrichByFile($event->getClass()->asDom());
        }

        public function enrichTrait(TraitStartEvent $event) {
            $this->enrichByFile($event->getTrait()->asDom());
        }

        public function enrichTokenFile(TokenFileStartEvent $event) {
            try {
                $tokenDom = $event->getTokenFile()->asDom();
                $coverageDom = $this->loadConverageInformation($tokenDom);
                $coverage = $coverageDom->queryOne('//pu:coverage[pu:line]');
                if ($coverage) {
                    $container = $this->getEnrichtmentContainer($tokenDom->documentElement, 'phpunit');
                    $container->appendChild($tokenDom->importNode($coverage, true));
                }
            } catch (PHPUnitEnricherException $e) {
                // Silently ignore for now
            }

        }

        private function enrichByFile(fDOMDocument $dom) {
            try {
                $coverageDom = $this->loadConverageInformation($dom);
                $this->processUnit($dom, $coverageDom);
            } catch (PHPUnitEnricherException $e) {
                // Silently ignore for now
            }
        }

        /**
         * @param $fname
         *
         * @return fDOMDocument
         *
         * @throws EnricherException
         */
        private function loadXML($fname) {
            try {
                $fname = (string)$this->coveragePath . '/' . $fname;
                if (!file_exists($fname)) {
                    throw new EnricherException(
                        sprintf('PHPUnit xml file "%s" not found.', $fname),
                        EnricherException::LoadError
                    );
                }
                $dom = new fDOMDocument();
                $dom->load($fname);
                $dom->registerNamespace('pu', 'http://schema.phpunit.de/coverage/1.0');
                return $dom;
            } catch (fDOMException $e) {
                throw new EnricherException(
                    'Parsing PHPUnit xml file failed: ' . $e->getMessage(),
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
            if (!$classNode) {
                // This class seems to be newer than the last phpunit run
                return;
            }
            $coverageTarget = $enrichment->appendElementNS(self::XMLNS, 'coverage');
            foreach(array('executable','executed', 'crap') as $attr) {
                $coverageTarget->appendChild(
                    $coverageTarget->ownerDocument->importNode($classNode->getAttributeNode($attr))
                );
            }

            $result = array(
                'PASSED'  => 0,
                'SKIPPED'  => 0,
                'INCOMPLETE'  => 0,
                'FAILURE'  => 0,
                'ERROR'  => 0,
                'RISKY'  => 0
            );

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
                    $test = $coverageTarget->appendChild(
                        $unit->importNode(
                            $this->index->queryOne(
                                sprintf('//pu:tests/pu:test[@name = %s]', $name)
                            )
                        )
                    );

                    $result[$test->getAttribute('status')]++;

                }

            }

            if (!isset($this->results[$classNamespace])) {
                $this->results[$classNamespace] = array();
                $this->coverage[$classNamespace] = array();
            }
            $this->results[$classNamespace][$className] = $result;
            $this->coverage[$classNamespace][$className] = $coverageTarget->cloneNode(false);
        }

        /**
         * @return FileInfo
         */
        private function getSourceDirectory() {
            $baseDir = $this->index->queryOne('//pu:project/pu:directory');
            $srcDir = new FileInfo($baseDir->getAttribute('name'));
            return $srcDir;
        }

        /**
         * @param fDOMDocument $dom
         *
         * @return fDOMDocument
         *
         * @throws EnricherException
         * @throws PHPUnitEnricherException
         */
        private function loadConverageInformation(fDOMDocument $dom) {
            $fileNode = $dom->queryOne('//phpdox:file');
            if (!$fileNode) {
                throw new PHPUnitEnricherException('No file header in event dom');
            }

            $fileInfo = new FileInfo($fileNode->getAttribute('path'));
            $srcDir = $this->getSourceDirectory();

            $paths = explode('/', (string)$fileInfo->getRelative($srcDir));
            $file = $fileNode->getAttribute('file');
            $paths = array_slice($paths, 1);

            $query = sprintf('//pu:project/pu:directory[@name = "%s"]', $srcDir->getRealPath());
            foreach ($paths as $path) {
                $query .= sprintf('/pu:directory[@name = "%s"]', $path);
            }
            $query .= sprintf('/pu:file[@name = "%s"]', $file);

            $phpunitFileNode = $this->index->queryOne($query);
            if (!$phpunitFileNode) {
                throw new PHPUnitEnricherException('No coverage information for file');
            }

            $refDom = $this->loadXML($phpunitFileNode->getAttribute('href'));
            return $refDom;
        }
    }


    class PHPUnitEnricherException extends EnricherException {
    }
}
