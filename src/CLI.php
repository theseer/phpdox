<?php declare(strict_types = 1);
namespace TheSeer\phpDox;

use TheSeer\fDOM\fDOMException;

class CLI {
    public const ExitOK = 0;

    public const ExitExecError = 1;

    public const ExitEnvError = 2;

    public const ExitParamError = 3;

    public const ExitConfigError = 4;

    public const ExitException = 5;

    /**
     * @var Environment
     */
    private $environment;

    /**
     * @var Version
     */
    private $version;

    /**
     * Factory instance
     *
     * @var Factory
     */
    private $factory;

    public function __construct(Environment $env, Version $version, Factory $factory) {
        $this->environment = $env;
        $this->version     = $version;
        $this->factory     = $factory;
    }

    /**
     * Main executor for CLI process.
     */
    public function run(CLIOptions $options) {
        $errorHandler = $this->factory->getErrorHandler();
        $errorHandler->register();

        try {
            $this->environment->ensureFitness();

            if ($options->showHelp() === true) {
                $this->showVersion();
                print $options->getHelpScreen();

                return self::ExitOK;
            }

            if ($options->showVersion() === true) {
                $this->showVersion();

                return self::ExitOK;
            }

            if ($options->generateSkel() === true) {
                $this->showSkeletonConfig($options->generateStrippedSkel());

                return self::ExitOK;
            }

            $config = $this->loadConfig($options);

            if ($config->isSilentMode()) {
                $this->factory->activateSilentMode();
            } else {
                $this->showVersion();
            }

            $logger = $this->factory->getLogger();
            $logger->log("Using config file '" . $config->getConfigFile()->getPathname() . "'");

            $app = $this->factory->getApplication();

            $defBootstrapFiles = new FileInfoCollection();
            $defBootstrapFiles->add(new FileInfo(__DIR__ . '/../bootstrap/backends.php'));
            $defBootstrapFiles->add(new FileInfo(__DIR__ . '/../bootstrap/enrichers.php'));
            $defBootstrapFiles->add(new FileInfo(__DIR__ . '/../bootstrap/engines.php'));

            $bootstrap = $app->runBootstrap($defBootstrapFiles);
            $bootstrap->load($config->getCustomBootstrapFiles(), false);

            if ($options->listEngines()) {
                $this->showVersion();
                $this->showList('engines', $bootstrap->getEngines());
            }

            if ($options->listEnrichers()) {
                $this->showVersion();
                $this->showList('enrichers', $bootstrap->getEnrichers());
            }

            if ($options->listBackends()) {
                $this->showVersion();
                $this->showList('backends', $bootstrap->getBackends());
            }

            if ($options->listBackends() || $options->listEngines() || $options->listEnrichers()) {
                return self::ExitOK;
            }

            foreach ($config->getProjects() as $projectName => $projectConfig) {
                $logger->log("Starting to process project '$projectName'");

                $app->runConfigChangeDetection(
                    $projectConfig->getWorkDirectory(),
                    $config->getConfigFile()
                );

                if (!$options->generatorOnly()) {
                    $app->runCollector($projectConfig->getCollectorConfig());
                }

                if (!$options->collectorOnly()) {
                    $app->runGenerator($projectConfig->getGeneratorConfig());
                }

                $logger->log("Processing project '$projectName' completed.");
            }

            $logger->buildSummary();

            return self::ExitOK;
        } catch (EnvironmentException $e) {
            $this->showVersion();
            \fwrite(\STDERR, 'Sorry, but your PHP environment is currently not able to run phpDox due to');
            \fwrite(\STDERR, "\nthe following issue(s):\n\n" . $e->getMessage() . "\n\n");
            \fwrite(\STDERR, "Please adjust your PHP configuration and try again.\n\n");

            return self::ExitEnvError;
        } catch (CLIOptionsException $e) {
            $this->showVersion();
            \fwrite(\STDERR, $e->getMessage() . "\n\n");
            \fwrite(\STDERR, $options->getHelpScreen());

            return self::ExitParamError;
        } catch (ConfigLoaderException $e) {
            $this->showVersion();
            \fwrite(\STDERR, "\nAn error occured while trying to load the configuration file:\n\n" . $e->getMessage() . "\n\n");

            if ($e->getCode() == ConfigLoaderException::NeitherCandidateExists) {
                \fwrite(\STDERR, "Using --skel might get you started.\n\n");
            }

            return self::ExitConfigError;
        } catch (ConfigException $e) {
            \fwrite(\STDERR, "\nYour configuration seems to be corrupted:\n\n\t" . $e->getMessage() . "\n\nPlease verify your configuration xml file.\n\n");

            return self::ExitConfigError;
        } catch (ApplicationException $e) {
            \fwrite(\STDERR, "\nAn application error occured while processing:\n\n\t" . $e->getMessage() . "\n\nPlease verify your configuration.\n\n");

            return self::ExitExecError;
        } catch (\Exception $e) {
            if ($e instanceof fDOMException) {
                $e->toggleFullMessage(true);
            }
            $this->showVersion();
            $errorHandler->handleException($e);

            return self::ExitException;
        } catch (\Throwable $e) {
            $this->showVersion();
            $errorHandler->handleException($e);

            return self::ExitException;
        }
    }

    /**
     * Helper to output version information.
     */
    private function showVersion(): void {
        static $shown = false;

        if ($shown) {
            return;
        }
        $shown = true;
        print $this->version->getInfoString() . "\n\n";
    }

    private function showSkeletonConfig($strip): void {
        $skel = $this->factory->getConfigSkeleton();
        print $strip ? $skel->renderStripped() : $skel->render();
    }

    private function showList($title, array $list): void {
        print "\nThe following $title are registered:\n\n";

        foreach ($list as $name => $desc) {
            \printf("   %s \t %s\n", $name, $desc);
        }
        print "\n\n";
    }

    /**
     * @throws ConfigLoaderException
     */
    private function loadConfig(CLIOptions $options): GlobalConfig {
        $cfgLoader = $this->factory->getConfigLoader();
        $cfgFile   = $options->configFile();

        if ($cfgFile != '') {
            return $cfgLoader->load($cfgFile);
        }

        return $cfgLoader->autodetect();
    }
}
