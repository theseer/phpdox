<?php declare(strict_types = 1);
namespace TheSeer\phpDox;

use TheSeer\DirectoryScanner\DirectoryScanner;
use TheSeer\phpDox\Collector\Collector;
use TheSeer\phpDox\Collector\InheritanceResolver;
use TheSeer\phpDox\Collector\Project;
use TheSeer\phpDox\Generator\Engine\EventHandlerRegistry;
use TheSeer\phpDox\Generator\Generator;

class Factory {
    /**
     * @var FileInfo
     */
    private $homeDir;

    /**
     * @var Version
     */
    private $version;

    /**
     * @var array
     */
    private $instances = [];

    /**
     * @var bool
     */
    private $isSilentMode = false;

    public function __construct(FileInfo $home, Version $version) {
        $this->homeDir = $home;
        $this->version = $version;
    }

    public function activateSilentMode(): void {
        $this->isSilentMode = true;
    }

    public function getErrorHandler(): ErrorHandler {
        return new ErrorHandler($this->version);
    }

    public function getCLI(): CLI {
        return new CLI(new Environment(), $this->version, $this);
    }

    public function getConfigLoader(): ConfigLoader {
        return new ConfigLoader($this->version, $this->homeDir);
    }

    public function getConfigSkeleton() {
        return new ConfigSkeleton(
            new FileInfo(__DIR__ . '/../config/skeleton.xml')
        );
    }

    public function getDirectoryCleaner(): DirectoryCleaner {
        return new DirectoryCleaner();
    }

    public function getLogger(): ProgressLogger {
        if (!isset($this->instances['logger'])) {
            $this->instances['logger'] = $this->isSilentMode ? $this->getSilentProgressLogger() : $this->getShellProgressLogger();
        }

        return $this->instances['logger'];
    }

    public function getBootstrap(): Bootstrap {
        return new Bootstrap($this->getLogger(), $this->getBootstrapApi());
    }

    public function getApplication(): Application {
        return new Application($this, $this->getLogger());
    }

    /**
     * @param array|string $include
     * @param array|string $exclude
     *
     * @return mixed|object
     */
    public function getScanner($include, $exclude = null) {
        $scanner = $this->getDirectoryScanner();

        if (\is_array($include)) {
            $scanner->setIncludes($include);
        } else {
            $scanner->addInclude($include);
        }

        if ($exclude != null) {
            if (\is_array($exclude)) {
                $scanner->setExcludes($exclude);
            } else {
                $scanner->addExclude($exclude);
            }
        }
        $scanner->setFlag(\FilesystemIterator::UNIX_PATHS);

        return $scanner;
    }

    public function getCollector(CollectorConfig $config): Collector {
        return new Collector(
            $this->getLogger(),
            new Project(
                $config->getSourceDirectory(),
                $config->getWorkDirectory()
            ),
            $this->getBackendFactory()->getInstanceFor($config->getBackend()),
            $config->getFileEncoding(),
            $config->isPublicOnlyMode()
        );
    }

    public function getInheritanceResolver(): InheritanceResolver {
        return new \TheSeer\phpDox\Collector\InheritanceResolver($this->getLogger());
    }

    public function getGenerator(): Generator {
        return new Generator($this->getLogger(), new EventHandlerRegistry());
    }

    /**
     * @return \TheSeer\phpDox\DocBlock\Factory
     */
    public function getDocblockFactory(): DocBlock\Factory {
        if (!isset($this->instances['DocblockFactory'])) {
            $this->instances['DocblockFactory'] = new \TheSeer\phpDox\DocBlock\Factory($this);
        }

        return $this->instances['DocblockFactory'];
    }

    public function getBackendFactory() {
        if (!isset($this->instances['BackendFactory'])) {
            $this->instances['BackendFactory'] = new \TheSeer\phpDox\Collector\Backend\Factory($this);
        }

        return $this->instances['BackendFactory'];
    }

    public function getEngineFactory() {
        if (!isset($this->instances['EngineFactory'])) {
            $this->instances['EngineFactory'] = new \TheSeer\phpDox\Generator\Engine\Factory();
        }

        return $this->instances['EngineFactory'];
    }

    public function getEnricherFactory() {
        if (!isset($this->instances['EnricherFactory'])) {
            $this->instances['EnricherFactory'] = new \TheSeer\phpDox\Generator\Enricher\Factory();
        }

        return $this->instances['EnricherFactory'];
    }

    public function getDocblockParser() {
        if (!isset($this->instances['DocblockParser'])) {
            $this->instances['DocblockParser'] = new \TheSeer\phpDox\DocBlock\Parser($this->getDocblockFactory());
        }

        return $this->instances['DocblockParser'];
    }

    private function getDirectoryScanner(): DirectoryScanner {
        return new DirectoryScanner();
    }

    private function getBootstrapApi(): BootstrapApi {
        return new BootstrapApi($this->getBackendFactory(), $this->getDocblockFactory(), $this->getEnricherFactory(), $this->getEngineFactory(), $this->getLogger());
    }

    private function getSilentProgressLogger() {
        return new SilentProgressLogger();
    }

    private function getShellProgressLogger() {
        return new ShellProgressLogger();
    }
}
