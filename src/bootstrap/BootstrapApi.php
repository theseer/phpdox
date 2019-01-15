<?php declare(strict_types = 1);
namespace TheSeer\phpDox;

use TheSeer\phpDox\Collector\Backend\Factory as BackendFactory;
use TheSeer\phpDox\DocBlock\Factory as DocBlockFactory;
use TheSeer\phpDox\Generator\Engine\Factory as EngineFactory;
use TheSeer\phpDox\Generator\Enricher\Factory as EnricherFactory;

/**
 * Bootstrapping API for registering backends, generator engines and parsers
 *
 * This class provides the API for use within the bootstrap process to register
 * collecting backends, additional parsers for annotations, generator engines for
 * additional output formats as well as enrichment plugins
 *
 * @author     Arne Blankerts <arne@blankerts.de>
 * @copyright  Arne Blankerts <arne@blankerts.de>, All rights reserved.
 * @license    BSD License
 */
class BootstrapApi {
    /**
     * Reference to the BackendFactory instance
     *
     * @var BackendFactory
     */
    private $backendFactory;

    /**
     * Reference to the EngineFactory instance
     *
     * @var EngineFactory
     */
    private $engineFactory;

    /**
     * Reference to the DocblockParserFactory instance
     *
     * @var DocBlockFactory
     */
    private $parserFactory;

    /**
     * @var EnricherFactory
     */
    private $enricherFactory;

    /**
     * Array of registered engines
     *
     * @var array
     */
    private $engines = [];

    /**
     * Array of registered enrichers
     *
     * @var array
     */
    private $enrichers = [];

    /**
     * Array of registered backends
     *
     * @var array
     */
    private $backends = [];

    /**
     * Constructor
     */
    public function __construct(BackendFactory $bf, DocBlockFactory $df, EnricherFactory $erf, EngineFactory $enf, ProgressLogger $logger) {
        $this->backendFactory  = $bf;
        $this->engineFactory   = $enf;
        $this->parserFactory   = $df;
        $this->enricherFactory = $erf;
        $this->logger          = $logger;
    }

    /**
     * Get list of all registered generator engines
     */
    public function getEngines(): array {
        return $this->engines;
    }

    /**
     * Get list of all registered enrichers
     */
    public function getEnrichers(): array {
        return $this->enrichers;
    }

    /**
     * Get list of all registered collector backends
     */
    public function getBackends(): array {
        return $this->backends;
    }

    /**
     * Register a new backend
     *
     * @param string $name        Name of the collector backend
     * @param string $description A describing text
     */
    public function registerBackend($name, $description = 'no description set'): BackendBootstrapApi {
        $this->logger->log("Registered collector backend '$name'");
        $this->backends[$name] = $description;

        return new BackendBootstrapApi($name, $this->backendFactory);
    }

    /**
     * Register a new generator enginge
     *
     * @param string $name        Name of the generator engine
     * @param string $description A describing text
     */
    public function registerEngine($name, $description): EngineBootstrapApi {
        $this->logger->log("Registered output engine '$name'");
        $this->engines[$name] = $description;

        return new EngineBootstrapApi($name, $this->engineFactory);
    }

    /**
     * @param $annotation
     */
    public function registerParser($annotation): ParserBootstrapApi {
        $this->logger->log("Registered parser for '$annotation' annotation");

        return new ParserBootstrapApi($annotation, $this->parserFactory);
    }

    /**
     * Register a new enricher
     *
     * @param string $name        Name of the enricher
     * @param string $description A describing text
     */
    public function registerEnricher($name, $description): EnricherBootstrapApi {
        $this->logger->log("Registered enricher '$name'");
        $this->enrichers[$name] = $description;

        return new EnricherBootstrapApi($name, $this->enricherFactory);
    }
}
