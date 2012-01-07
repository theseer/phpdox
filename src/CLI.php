<?php
/**
 * Copyright (c) 2010-2011 Arne Blankerts <arne@blankerts.de>
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

    use TheSeer\fDOM\fDOMException;

    class CLI {

        /**
         * Version identifier
         *
         * @var string
         */
        const VERSION = "%version%";

        /**
         * Factory instance
         *
         * @var Factory
         */
        protected $factory;

        public function __construct(Factory $factory) {
            $this->factory = $factory;
        }

        /**
         * Main executor for CLI process.
         */
        public function run() {
            error_reporting(-1);
            $errorHandler = $this->factory->getInstanceFor('ErrorHandler');
            $errorHandler->register();
            try {
                $input = $this->registerOptions();
                $input->process();

                if ($input->getOption('version')->value === true) {
                    $this->showVersion();
                    exit(0);
                }

                if ($input->getOption('help')->value === true) {
                    $this->showVersion();
                    $this->showUsage();
                    exit(0);
                }

                $errorHandler->setDebug($input->getOption('debug')->value);

                $cfgLoader = $this->factory->getInstanceFor('ConfigLoader');
                $cfgFile = $input->getOption('file')->value;
                if ($cfgFile) {
                    $config = $cfgLoader->load($cfgFile);
                } else {
                    $config = $cfgLoader->autodetect();
                }

                if ($config->isSilentMode()) {
                    $this->factory->setLoggerType('silent');
                } else {
                    $this->showVersion();
                    $this->factory->setLoggerType('shell');
                }

                $logger = $this->factory->getLogger();
                $logger->log("Using config file '". $config->getFilename(). "'");

                $app = $this->factory->getInstanceFor('Application');
                $engines = $app->runBootstrap($config->getBootstrapFiles());

                if ($input->getOption('engines')->value) {
                    $this->showVersion();
                    $this->showEngines($engines);
                    exit(0);
                }

                foreach($config->getAvailableProjects() as $project) {
                    $logger->log("Starting to process project '$project'");
                    $pcfg = $config->getProjectConfig($project);

                    if (!$input->getOption('generator')->value) {
                        $app->runCollector( $pcfg->getCollectorConfig() );
                    }

                    if (!$input->getOption('collector')->value) {
                        $app->runGenerator( $pcfg->getGeneratorConfig() );
                    }

                    $logger->log("Processing project '$project' completed.");

                }

                $logger->buildSummary();

            } catch (\ezcConsoleException $e) {
                $this->showVersion();
                fwrite(STDERR, "\n".$e->getMessage()."\n\n");
                $this->showUsage();
                exit(3);
            } catch (ConfigLoaderException $e) {
                $this->showVersion();
                fwrite(STDERR, "\nAn error occured while trying to load the configuration file:\n" . $e->getMessage()."\n\n");
                exit(3);
            } catch (ConfigException $e) {
                fwrite(STDERR, "\nYour configuration seems to be corrupted:\n\n\t" . $e->getMessage()."\n\nPlease verify your configuration xml file.\n\n");
                exit(3);
            } catch (ApplicationException $e) {
                fwrite(STDERR, "\nAn application error occured while processing:\n\n\t" . $e->getMessage()."\n\nPlease verify your configuration.\n\n");
                exit(3);
            } catch (\Exception $e) {
                if ($e instanceof fDOMException) {
                    $e->toggleFullMessage(true);
                }
                $this->showVersion();
                $errorHandler->handleException($e);
            }
        }

        /**
         * Helper to output version information.
         */
        protected function showVersion() {
            static $shown = false;
            if ($shown) {
                return;
            }
            $shown = true;
            printf("phpdox %s - Copyright (C) 2010 - 2011 by Arne Blankerts\n\n", self::VERSION);
        }

        protected function showEngines(Array $list) {
            echo "\nThe following engines are registered:\n\n";
            foreach($list as $name => $desc) {
                printf("   %s \t %s\n", $name, $desc);
            }
            echo "\n\n";
        }

        /**
         * Helper to register supported CLI options into an ezcConsoleInput
         *
         * @return \ezcConsoleInput $input ezcConsoleInput instance options get registered in to
         */
        protected function registerOptions() {
            $input = new \ezcConsoleInput();
            $versionOption = $input->registerOption( new \ezcConsoleOption( 'v', 'version' ) );
            $versionOption->shorthelp    = 'Prints the version and exits';
            $versionOption->isHelpOption = true;

            $helpOption = $input->registerOption( new \ezcConsoleOption( 'h', 'help' ) );
            $helpOption->isHelpOption = true;
            $helpOption->shorthelp    = 'Prints this usage information';

            $input->registerOption( new \ezcConsoleOption(
                'f', 'file', \ezcConsoleInput::TYPE_STRING, null, true,
                'Configuration file to load'
            ));

            $c = $input->registerOption( new \ezcConsoleOption(
                    'c', 'collector', \ezcConsoleInput::TYPE_NONE, null, false,
                    'Run collector process only'
            ));

            $g = $input->registerOption( new \ezcConsoleOption(
                    'g', 'generator', \ezcConsoleInput::TYPE_NONE, null, false,
                    'Run generator process only'
            ));

            $g->addExclusion(new \ezcConsoleOptionRule($c));
            $c->addExclusion(new \ezcConsoleOptionRule($g));

            $input->registerOption( new \ezcConsoleOption(
                null, 'debug', \ezcConsoleInput::TYPE_NONE, null, false,
                'For plugin developers only, enable php error reporting'
            ));
            $input->registerOption( new \ezcConsoleOption(
                null, 'engines', \ezcConsoleInput::TYPE_NONE, null, false,
                'Show a list of available engines and exit'
            ));

            return $input;
        }

        /**
         * Helper to output usage information.
         */
        protected function showUsage() {
            print <<<EOF
Usage: phpdox [switches]

  -f, --file       Configuration file to use (defaults to ./phpdox.xml[.dist])

  -h, --help       Prints this usage information
  -v, --version    Prints the version and exits

      --debug      For plugin developers only, enable php error reporting

      --engines    Show a list of available output engines and exit


EOF;
        }

    }

}
