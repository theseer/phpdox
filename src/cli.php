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
            try {
                $input = new \ezcConsoleInput();
                $this->registerOptions($input);
                $input->process();

                if ((!$input->getOption('collect')->value && !$input->getOption('generate')->value) ||
                    $input->getOption('help')->value === true) {
                    $this->showVersion();
                    $this->showUsage();
                    exit(0);
                }

                if ($input->getOption('version')->value === true) {
                    $this->showVersion();
                    exit(0);
                }

                if ($input->getOption('silent')->value === true) {
                    $logger = $this->factory->getLogger('silent');
                } else {
                    $this->showVersion();
                    $logger = $this->factory->getLogger('shell');
                }

                $this->factory->setXMLDir($input->getOption('xml')->value);

                $app = $this->factory->getApplication();
                $app->setLogger($logger);
                $app->loadBootstrap($input->getOption('require')->value);

                if ($path = $input->getOption('collect')->value) {
                    $path = realpath($path);
                    $app->runCollector(
                        $path,
                        $this->getScanner($path, $input),
                        $input->getOption('public')->value
                    );
                }
                if ($generate = $input->getOption('generate')->value) {
                    $app->runGenerator(
                        $generate,
                        $input->getOption('templates')->value,
                        $input->getOption('docs')->value,
                        $input->getOption('public')->value
                    );
                }

                $logger->buildSummary();

            } catch (fDOMException $e) {
                fwrite(STDERR, "XML Error while processing request:\n");
                fwrite(STDERR, $e->getFullMessage()."\n" . $e->getTraceAsString());
                fwrite(STDERR, "\n\nPlease file a bugreport for this!\n");
                exit(1);
            } catch (\ezcConsoleException $e) {
                $this->showVersion();
                fwrite(STDERR, $e->getMessage()."\n\n");
                $this->showUsage();
                exit(3);
            } catch (CLIException $e) {
                $this->showVersion();
                fwrite(STDERR, "Error while processing request:\n");
                fwrite(STDERR, $e->getMessage()."\n");
                exit(3);
            } catch (\Exception $e) {
                $this->showVersion();
                fwrite(STDERR, "Unexpected error while processing request:\n");
                fwrite(STDERR, ' - ' . $e."\n");
                fwrite(STDERR, "\n\nPlease file a bugreport for this!\n");
                exit(1);
            }
        }

        /**
         * Helper to get instance of DirectoryScanner with cli options applied
         *
         * @param string          $path  Path to get iterator scanner for
         * @param ezcConsoleInput $input CLI Options pased to app
         *
         * @return Theseer\Tools\IncludeExcludeFilterIterator
         */
        protected function getScanner($path, \ezcConsoleInput $input) {
            $scanner = $this->factory->getScanner(
                $input->getOption('include')->value,
                $input->getOption('exclude')->value
            );
            return $scanner($path);
        }

        /**
         * Helper to output version information.
         */
        protected function showVersion() {
            printf("phpdox %s - Copyright (C) 2010 - 2011 by Arne Blankerts\n\n", self::VERSION);
        }

        /**
         * Helper to register supported CLI options into ezcConsoleInput
         *
         * @param \ezcConsoleInput $input ezcConsoleInput instance to register options in to
         */
        protected function registerOptions(\ezcConsoleInput $input) {
            $versionOption = $input->registerOption( new \ezcConsoleOption( 'v', 'version' ) );
            $versionOption->shorthelp    = 'Prints the version and exits';
            $versionOption->isHelpOption = true;

            $helpOption = $input->registerOption( new \ezcConsoleOption( 'h', 'help' ) );
            $helpOption->isHelpOption = true;
            $helpOption->shorthelp    = 'Prints this usage information';

            $input->registerOption( new \ezcConsoleOption(
                'i', 'include', \ezcConsoleInput::TYPE_STRING, '*.php', true,
                'File pattern to include (default: *.php)'
            ));
            $input->registerOption( new \ezcConsoleOption(
                'e', 'exclude', \ezcConsoleInput::TYPE_STRING, null, true,
                'File pattern to exclude'
            ));

            $input->registerOption( new \ezcConsoleOption(
                'x', 'xml', \ezcConsoleInput::TYPE_STRING, './xml', false,
                'Output directory for collected data (default: ./xml)'
            ));
            $input->registerOption( new \ezcConsoleOption(
                'd', 'docs', \ezcConsoleInput::TYPE_STRING, './docs', false,
                'Output directory for generated documentation (default: ./docs)'
            ));
            $input->registerOption( new \ezcConsoleOption(
                'p', 'public', \ezcConsoleInput::TYPE_NONE, null, false,
                'Only show public member and methods'
            ));

            $input->registerOption( new \ezcConsoleOption(
                'g', 'generate', \ezcConsoleInput::TYPE_STRING, null, true,
                'generate documentation'
            ));
            $col = $input->registerOption( new \ezcConsoleOption(
                'c', 'collect', \ezcConsoleInput::TYPE_STRING, null, false,
                'collect data in given source directory'
            ));

            $input->registerOption( new \ezcConsoleOption(
                's', 'silent', \ezcConsoleInput::TYPE_NONE, null, false,
                'Do not output anything to the console'
            ));
            $input->registerOption( new \ezcConsoleOption(
                'l', 'log', \ezcConsoleInput::TYPE_STRING, null, false,
                'Generate XML style logfile'
            ));
            $input->registerOption( new \ezcConsoleOption(
                'f', 'file', \ezcConsoleInput::TYPE_STRING, './phpdox.xml', true,
                'Configuration file to load'
            ));
            $input->registerOption( new \ezcConsoleOption(
                'r', 'require', \ezcConsoleInput::TYPE_STRING, array(), true,
                'Custom PHP Source file to load'
            ));
            $input->registerOption( new \ezcConsoleOption(
                't', 'templates', \ezcConsoleInput::TYPE_STRING, __DIR__ . '/../templates', false,
                'Output directory for collected data (default: ./xml)'
            ));
        }

        /**
         * Helper to output usage information.
         */
        protected function showUsage() {
            print <<<EOF
Usage: phpdox [switches]

  -f, --file       Configuration file to use (default: ./phpdox.xml)

  -c, --collect    Scan directory and collect input (default: ./src)
  -g, --generate   Generate documentation (default builder: html)

  -p, --public     Only process public member and methods

  -x, --xml        Output directory for collected data (default: ./xml)
  -d, --docs       Output directory for generated documentation (default: ./docs)
  -t, --templates  Overwrite directory to load templates from

  -l, --log        Generate XML style logfile (not implemented yet)
  -s, --silent     Do not output anything to the console

  -i, --include    File pattern to include (default: *.php)
  -e, --exclude    File pattern to exclude

  -r, --require    Load additional bootstrap files

  -h, --help       Prints this usage information
  -v, --version    Prints the version and exits


EOF;
        }

    }

    class CLIException extends \Exception {
        const NoProcessing = 1;
    }

}