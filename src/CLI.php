<?php
/**
 * Copyright (c) 2010-2015 Arne Blankerts <arne@blankerts.de>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 *   * Redistributions of source code must retain the above copyright notice,
 *     this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright notice,
 *     this list of conditions and the following disclaimer in the documentation
 *     and/or other materials provided with the distribution.
 *
 *   * Neither the name of Arne Blankerts nor the names of contributors
 *     may be used to endorse or promote products derived from this software
 *     without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT  * NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER ORCONTRIBUTORS
 * BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 * OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * Exit codes:
 *   0 - No error
 *   1 - Execution Error
 *   3 - Parameter Error
 *
 * @package    phpDox
 * @author     Arne Blankerts <arne@blankerts.de>
 * @copyright  Arne Blankerts <arne@blankerts.de>, All rights reserved.
 * @license    BSD License
 *
 */
namespace TheSeer\phpDox {

    use TheSeer\fDOM\fDOMDocument;
    use TheSeer\fDOM\fDOMException;

    class CLI {

        const NO_ERROR_CODE = 0;
        const EXECUTION_ERROR_CODE = 1;
        const PARAMETER_ERROR_CODE = 3;

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

        /**
         * @var \Exception
         */
        private $lastException;

        /**
         * @param Environment $env
         * @param Factory     $factory
         */
        public function __construct(Environment $env, Version $version, Factory $factory) {
            $this->environment = $env;
            $this->version = $version;
            $this->factory = $factory;
        }

        /**
         * Main executor for CLI process.
         */
        public function run(CLIOptions $options) {
            $this->lastException = null;
            $errorHandler = $this->factory->getErrorHandler();
            $errorHandler->register();
            try {
                $this->environment->ensureFitness();

                if ($options->showHelp() === TRUE) {
                    $this->showVersion();
                    echo $options->getHelpScreen();
                    return self::NO_ERROR_CODE;
                }

                if ($options->showVersion() === TRUE) {
                    $this->showVersion();
                    return self::NO_ERROR_CODE;
                }

                if ($options->generateSkel() === TRUE) {
                    $this->showSkeletonConfig($options->generateStrippedSkel());
                    return self::NO_ERROR_CODE;
                }

                $cfgLoader = $this->factory->getConfigLoader();
                $cfgFile = $options->configFile();
                if ($cfgFile != '') {
                    $config = $cfgLoader->load($cfgFile);
                } else {
                    $config = $cfgLoader->autodetect();
                }

                /** @var $config GlobalConfig */
                if ($config->isSilentMode()) {
                    $this->factory->activateSilentMode();
                } else {
                    $this->showVersion();
                }

                $logger = $this->factory->getLogger();
                $logger->log("Using config file '". $config->getConfigFile()->getPathname() . "'");

                /** @var Application $app */
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
                    return self::NO_ERROR_CODE;
                }

                foreach($config->getProjects() as $projectName => $projectConfig) {

                    /** @var ProjectConfig $projectConfig */
                    $logger->log("Starting to process project '$projectName'");

                    $app->runConfigChangeDetection(
                        $projectConfig->getWorkDirectory(),
                        $config->getConfigFile()
                    );

                    if (!$options->generatorOnly()) {
                        $app->runCollector( $projectConfig->getCollectorConfig() );
                    }

                    if (!$options->collectorOnly()) {
                        $app->runGenerator( $projectConfig->getGeneratorConfig() );
                    }

                    $logger->log("Processing project '$projectName' completed.");

                }

                $logger->buildSummary();

            } catch (EnvironmentException $e) {
                $this->showVersion();
                fwrite(STDERR, 'Sorry, but your PHP environment is currently not able to run phpDox due to');
                fwrite(STDERR, "\nthe following issue(s):\n\n" . $e->getMessage() . "\n\n");
                fwrite(STDERR, "Please adjust your PHP configuration and try again.\n\n");
                $this->lastException = $e;
                return self::PARAMETER_ERROR_CODE;
            } catch (CLIOptionsException $e) {
                $this->showVersion();
                fwrite(STDERR, $e->getMessage()."\n\n");
                fwrite(STDERR, $options->getHelpScreen());
                $this->lastException = $e;
                return self::PARAMETER_ERROR_CODE;
            } catch (ConfigLoaderException $e) {
                $this->showVersion();
                fwrite(STDERR, "\nAn error occured while trying to load the configuration file:\n\n" . $e->getMessage(). "\n\n");
                if ($e->getCode() == ConfigLoaderException::NeitherCandidateExists) {
                    fwrite(STDERR, "Using --skel might get you started.\n\n");
                }
                $this->lastException = $e;
                return self::PARAMETER_ERROR_CODE;
            } catch (ConfigException $e) {
                fwrite(STDERR, "\nYour configuration seems to be corrupted:\n\n\t" . $e->getMessage()."\n\nPlease verify your configuration xml file.\n\n");
                $this->lastException = $e;
                return self::PARAMETER_ERROR_CODE;
            } catch (ApplicationException $e) {
                fwrite(STDERR, "\nAn application error occured while processing:\n\n\t" . $e->getMessage()."\n\nPlease verify your configuration.\n\n");
                $this->lastException = $e;
                return self::EXECUTION_ERROR_CODE;
            } catch (\Exception $e) {
                if ($e instanceof fDOMException) {
                    $e->toggleFullMessage(TRUE);
                }
                $this->showVersion();
                $errorHandler->handleException($e);
                $this->lastException = $e;
                return EXECUTION_ERROR_CODE;
            }
        }

        /**
         * @return \Exception
         */
        public function getLastException()
        {
            return $this->lastException;
        }

        /**
         * Helper to output version information.
         */
        private function showVersion() {
            static $shown = FALSE;
            if ($shown) {
                return;
            }
            $shown = TRUE;
            echo $this->version->getInfoString() . "\n\n";
        }

        private function showSkeletonConfig($strip) {
            $skel = $this->factory->getConfigSkeleton();
            echo $strip ? $skel->renderStripped() : $skel->render();
        }

        private function showList($title, Array $list) {
            echo "\nThe following $title are registered:\n\n";
            foreach($list as $name => $desc) {
                printf("   %s \t %s\n", $name, $desc);
            }
            echo "\n\n";
        }

    }

}
