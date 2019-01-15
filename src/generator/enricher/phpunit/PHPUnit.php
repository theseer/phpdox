<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator\Enricher;

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
    public const XMLNS_HTTP = 'http://schema.phpunit.de/coverage/1.0';

    public const XMLNS_HTTPS = 'https://schema.phpunit.de/coverage/1.0';

    /**
     * @var Fileinfo
     */
    private $coveragePath;

    /**
     * @var string
     */
    private $namespaceURI;

    /**
     * @var FileInfo
     */
    private $sourceDirectory;

    /**
     * @var fDOMDocument
     */
    private $index;

    private $results = [];

    private $coverage = [];

    /**
     * @throws EnricherException
     */
    public function __construct(PHPUnitConfig $config) {
        $this->coveragePath    = $config->getCoveragePath();
        $this->sourceDirectory = $config->getSourceDirectory();

        $this->index = $this->loadXML('index.xml');
    }

    public function getName(): string {
        return 'PHPUnit Coverage XML';
    }

    public function enrichEnd(PHPDoxEndEvent $event): void {
        $index = $event->getIndex()->asDom();

        foreach ($this->results as $namespace => $classes) {
            foreach ($classes as $class => $results) {
                $classNode = $index->queryOne(
                    \sprintf('//phpdox:namespace[@name = "%s"]/phpdox:class[@name = "%s"]', $namespace, $class)
                );

                if (!$classNode) {
                    continue;
                }
                /** @var fDOMElement $classNode */
                $container  = $this->getEnrichtmentContainer($classNode, 'phpunit');
                $resultNode = $container->appendElementNS($this->namespaceURI, 'result');

                foreach ($results as $key => $value) {
                    $resultNode->setAttribute(\mb_strtolower($key), $value);
                }
                $container->appendChild(
                    $container->ownerDocument->importNode($this->coverage[$namespace][$class])
                );
            }
        }
    }

    public function enrichClass(ClassStartEvent $event): void {
        $this->enrichByFile($event->getClass()->asDom());
    }

    public function enrichTrait(TraitStartEvent $event): void {
        $this->enrichByFile($event->getTrait()->asDom());
    }

    public function enrichTokenFile(TokenFileStartEvent $event): void {
        try {
            $tokenDom    = $event->getTokenFile()->asDom();
            $coverageDom = $this->loadCoverageInformation($tokenDom);
            $coverage    = $coverageDom->queryOne('//pu:coverage[pu:line]');

            if ($coverage) {
                $container = $this->getEnrichtmentContainer($tokenDom->documentElement, 'phpunit');
                $container->appendChild($tokenDom->importNode($coverage, true));
            }
        } catch (PHPUnitEnricherException $e) {
            // Silently ignore for now
        }
    }

    private function enrichByFile(fDOMDocument $dom): void {
        try {
            $coverageDom = $this->loadCoverageInformation($dom);
            $this->processUnit($dom, $coverageDom);
        } catch (PHPUnitEnricherException $e) {
            // Silently ignore for now
        }
    }

    /**
     * @param $fname
     *
     * @throws EnricherException
     */
    private function loadXML($fname): fDOMDocument {
        try {
            $fname = (string)$this->coveragePath . '/' . $fname;

            if (!\file_exists($fname)) {
                throw new EnricherException(
                    \sprintf('PHPUnit coverage xml file "%s" not found.', $fname),
                    EnricherException::LoadError
                );
            }
            $dom = new fDOMDocument();
            $dom->load($fname);

            $this->namespaceURI = $dom->documentElement->namespaceURI;

            if (!\in_array($this->namespaceURI, [self::XMLNS_HTTP, self::XMLNS_HTTPS])) {
                throw new EnricherException(
                    'Wrong namspace - not a PHPUnit code coverage file',
                    EnricherException::LoadError
                );
            }

            $dom->registerNamespace('pu', $this->namespaceURI);

            return $dom;
        } catch (fDOMException $e) {
            throw new EnricherException(
                'Parsing PHPUnit xml file failed: ' . $e->getMessage(),
                EnricherException::LoadError
            );
        }
    }

    private function processUnit(fDOMDocument $unit, fDOMDocument $coverage): void {
        $enrichment = $this->getEnrichtmentContainer($unit->documentElement, 'phpunit');

        $className      = $unit->documentElement->getAttribute('name');
        $classNamespace = $unit->documentElement->getAttribute('namespace');

        $classNode = $coverage->queryOne(
            \sprintf('//pu:class[@name = "%2$s\%1$s" or (@name = "%1$s" and pu:namespace[@name = "%2$s"])]', $className, $classNamespace)
        );

        if (!$classNode) {
            // This class seems to be newer than the last phpunit run
            return;
        }
        $coverageTarget = $enrichment->appendElementNS($this->namespaceURI, 'coverage');

        foreach (['executable', 'executed', 'crap'] as $attr) {
            $coverageTarget->appendChild(
                $coverageTarget->ownerDocument->importNode($classNode->getAttributeNode($attr))
            );
        }

        $result = [
            'UNKNOWN'    => 0,
            'PASSED'     => 0,
            'SKIPPED'    => 0,
            'INCOMPLETE' => 0,
            'FAILURE'    => 0,
            'ERROR'      => 0,
            'RISKY'      => 0,
            'WARNING'    => 0
        ];

        $methods = $unit->query('/phpdox:*/phpdox:constructor|/phpdox:*/phpdox:destructor|/phpdox:*/phpdox:method');
        $xp      = $this->index->getDOMXPath();

        foreach ($methods as $method) {
            $start = $method->getAttribute('start');
            $end   = $method->getAttribute('end');

            $enrichment     = $this->getEnrichtmentContainer($method, 'phpunit');
            $coverageTarget = $enrichment->appendElementNS($this->namespaceURI, 'coverage');

            /** @var fDOMElement $coverageMethod */
            $coverageMethod = $coverage->queryOne(
                \sprintf('//pu:method[@start = "%d" and @end = "%d"]', $start, $end)
            );

            if ($coverageMethod != null) {
                foreach (['executable', 'executed', 'coverage', 'crap'] as $attr) {
                    $coverageTarget->appendChild(
                        $coverageTarget->ownerDocument->importNode($coverageMethod->getAttributeNode($attr))
                    );
                }
            }

            $coveredNodes = $coverage->query(
                \sprintf('//pu:coverage/pu:line[@nr >= "%d" and @nr <= "%d"]/pu:covered', $start, $end)
            );

            $seen = [];

            foreach ($coveredNodes as $coveredNode) {
                $by = $coveredNode->getAttribute('by');

                if (isset($seen[$by])) {
                    continue;
                }
                $seen[$by] = true;

                $name = $xp->prepare(':name', ['name' => $by]);
                $test = $coverageTarget->appendChild(
                    $unit->importNode(
                        $this->index->queryOne(
                            \sprintf('//pu:tests/pu:test[@name = %s]', $name)
                        )
                    )
                );

                $result[$test->getAttribute('status')]++;
            }
        }

        if (!isset($this->results[$classNamespace])) {
            $this->results[$classNamespace]  = [];
            $this->coverage[$classNamespace] = [];
        }
        $this->results[$classNamespace][$className]  = $result;
        $this->coverage[$classNamespace][$className] = $coverageTarget->cloneNode(false);
    }

    /**
     * @throws EnricherException
     * @throws PHPUnitEnricherException
     */
    private function loadCoverageInformation(fDOMDocument $dom): fDOMDocument {
        $fileNode = $dom->queryOne('//phpdox:file');

        if (!$fileNode) {
            throw new PHPUnitEnricherException('No file header in event dom');
        }

        $fileInfo = new FileInfo($fileNode->getAttribute('path'));
        $paths    = \explode('/', (string)$fileInfo->getRelative($this->sourceDirectory));
        $file     = $fileNode->getAttribute('file');
        $paths    = \array_slice($paths, 1);

        $query = \sprintf('//pu:project/pu:directory');

        foreach ($paths as $path) {
            $query .= \sprintf('/pu:directory[@name = "%s"]', $path);
        }
        $query .= \sprintf('/pu:file[@name = "%s"]', $file);

        $phpunitFileNode = $this->index->queryOne($query);

        if (!$phpunitFileNode) {
            throw new PHPUnitEnricherException('No coverage information for file');
        }

        return $this->loadXML($phpunitFileNode->getAttribute('href'));
    }
}

class PHPUnitEnricherException extends EnricherException {
}
