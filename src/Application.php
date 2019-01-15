<?php declare(strict_types = 1);
namespace TheSeer\phpDox;

use TheSeer\phpDox\Collector\InheritanceResolver;
use TheSeer\phpDox\Generator\Enricher\EnricherException;

/**
 * The main Application class
 *
 * @author     Arne Blankerts <arne@blankerts.de>
 * @copyright  Arne Blankerts <arne@blankerts.de>, All rights reserved.
 * @license    BSD License
 *
 * @link       http://phpDox.net
 */
class Application {
    /**
     * Logger for progress and error reporting
     *
     * @var ProgressLogger
     */
    private $logger;

    /**
     * Factory instance
     *
     * @var Factory
     */
    private $factory;

    /**
     * Constructor of PHPDox Application
     *
     * @param Factory        $factory Factory instance
     * @param ProgressLogger $logger  Instance of the SilentProgressLogger class
     */
    public function __construct(Factory $factory, ProgressLogger $logger) {
        $this->factory = $factory;
        $this->logger  = $logger;
    }

    /**
     * Run Bootstrap code for given list of bootstrap files
     */
    public function runBootstrap(FileInfoCollection $requires): Bootstrap {
        $bootstrap = $this->factory->getBootstrap();
        $bootstrap->load($requires, true);

        return $bootstrap;
    }

    public function runConfigChangeDetection(FileInfo $workDirectory, FileInfo $configFile): void {
        $index = new FileInfo((string)$workDirectory . '/index.xml');

        if (!$index->exists() || ($index->getMTime() >= $configFile->getMTime())) {
            return;
        }
        $this->logger->log('Configuration change detected - cleaning cache');
        $cleaner = $this->factory->getDirectoryCleaner();
        $cleaner->process($workDirectory);
    }

    /**
     * Run collection process on given directory tree
     *
     * @param CollectorConfig $config Configuration options
     *
     * @throws ApplicationException
     */
    public function runCollector(CollectorConfig $config): void {
        $this->logger->log('Starting collector');

        $srcDir = $config->getSourceDirectory();

        if (!$srcDir->isDir()) {
            throw new ApplicationException(
                \sprintf('Invalid src directory "%s" specified', $srcDir),
                ApplicationException::InvalidSrcDirectory
            );
        }

        $collector = $this->factory->getCollector($config);

        $scanner = $this->factory->getScanner(
            $config->getIncludeMasks(),
            $config->getExcludeMasks()
        );
        $project = $collector->run($scanner);

        if ($collector->hasParseErrors()) {
            $this->logger->log('The following file(s) had errors during processing and were excluded:');

            foreach ($collector->getParseErrors() as $file => $message) {
                $this->logger->log(' - ' . $file . ' (' . $message . ')');
            }
        }

        $this->logger->log(
            \sprintf("Saving results to directory '%s'", $config->getWorkDirectory())
        );
        $vanished = $project->cleanVanishedFiles();

        if (\count($vanished) > 0) {
            $this->logger->log(\sprintf('Removed %d vanished file(s) from project:', \count($vanished)));

            foreach ($vanished as $file) {
                $this->logger->log(' - ' . $file);
            }
        }
        $changed = $project->save();

        if ($config->doResolveInheritance()) {
            /** @var $resolver InheritanceResolver */
            $resolver = $this->factory->getInheritanceResolver();
            $resolver->resolve($changed, $project, $config->getInheritanceConfig());

            if ($resolver->hasUnresolved()) {
                $this->logger->log('The following unit(s) had missing dependencies during inheritance resolution:');

                foreach ($resolver->getUnresolved() as $class => $missing) {
                    if (\is_array($missing)) {
                        $missing = \implode(', ', $missing);
                    }
                    $this->logger->log(' - ' . $class . ' (missing ' . $missing . ')');
                }
            }

            if ($resolver->hasErrors()) {
                $this->logger->log('The following unit(s) caused errors during inheritance resolution:');

                foreach ($resolver->getErrors() as $class => $error) {
                    $this->logger->log(' - ' . $class . ': ' . \implode(', ', $error));
                }
            }
        }
        $this->logger->log("Collector process completed\n");
    }

    /**
     * Run Documentation generation process
     *
     * @throws ApplicationException
     */
    public function runGenerator(GeneratorConfig $config): void {
        $this->logger->reset();
        $this->logger->log('Starting generator');

        $engineFactory   = $this->factory->getEngineFactory();
        $enricherFactory = $this->factory->getEnricherFactory();

        $failed = \array_diff($config->getRequiredEngines(), $engineFactory->getEngineList());

        if (\count($failed)) {
            $list = \implode("', '", $failed);

            throw new ApplicationException("The engine(s) '$list' is/are not registered", ApplicationException::UnknownEngine);
        }

        $failed = \array_diff($config->getRequiredEnrichers(), $enricherFactory->getEnricherList());

        if (\count($failed)) {
            $list = \implode("', '", $failed);

            throw new ApplicationException("The enricher(s) '$list' is/are not registered", ApplicationException::UnknownEnricher);
        }

        $generator = $this->factory->getGenerator();

        foreach ($config->getActiveBuilds() as $buildCfg) {
            $generator->addEngine($engineFactory->getInstanceFor($buildCfg));
        }

        $this->logger->log('Loading enrichers');

        foreach ($config->getActiveEnrichSources() as $type => $enrichCfg) {
            try {
                $enricher = $enricherFactory->getInstanceFor($enrichCfg);
                $generator->addEnricher($enricher);
                $this->logger->log(
                    \sprintf('Enricher %s initialized successfully', $enricher->getName())
                );
            } catch (EnricherException $e) {
                $this->logger->log(
                    \sprintf(
                        "Exception while initializing enricher %s:\n\n    %s\n",
                        $type,
                        $e->getMessage()
                    )
                );
            }
        }

        $pconfig = $config->getProjectConfig();

        if (!\file_exists($pconfig->getWorkDirectory() . '/index.xml')) {
            throw new ApplicationException(
                'Workdirectory does not contain an index.xml file. Did you run the collector?',
                ApplicationException::IndexMissing
            );
        }

        if (!\file_exists($pconfig->getWorkDirectory() . '/source.xml')) {
            throw new ApplicationException(
                'Workdirectory does not contain an source.xml file. Did you run the collector?',
                ApplicationException::SourceMissing
            );
        }

        $srcDir = $pconfig->getSourceDirectory();

        if (!$srcDir->exists() || !$srcDir->isDir()) {
            throw new ApplicationException(
                \sprintf('Invalid src directory "%s" specified', $srcDir),
                ApplicationException::InvalidSrcDirectory
            );
        }

        $this->logger->log("Starting event loop.\n");
        $generator->run(
            new \TheSeer\phpDox\Generator\Project(
                $srcDir,
                $pconfig->getWorkDirectory()
            )
        );
        $this->logger->log('Generator process completed');
    }
}
