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
 * @package    phpDox
 * @author     Arne Blankerts <arne@blankerts.de>
 * @copyright  Arne Blankerts <arne@blankerts.de>, All rights reserved.
 * @license    BSD License
 *
 * Exit codes:
 *   0 - No error
 *   1 - Execution Error
 *   3 - Parameter Error
 *
 */
namespace TheSeer\phpDox {

    use \TheSeer\Tools\PHPFilterIterator;
    use \TheSeer\fDom\fDomDocument;

    class CLI {

        /**
         * Version identifier
         *
         * @var string
         */
        const VERSION = "%version%";

        /**
         * Main executor for CLI process.
         */
        public function run() {
            try {
                $input = new \ezcConsoleInput();
                $this->registerOptions($input);
                $input->process();

                if ($input->getOption('help')->value === true) {
                    $this->showVersion();
                    $this->showUsage();
                    exit(0);
                }

                if ($input->getOption('version')->value === true) {
                    $this->showVersion();
                    exit(0);
                }

                if ($require = $input->getOption('require')->value) {
                    $this->processRequire($require);
                }

                if ($input->getOption('silent')->value === true) {
                    $logger = new ProgressLogger();
                } else {
                    $this->showVersion();
                    $logger = new ShellProgressLogger();
                }

                $app = new Application($logger, $input->getOption('xml')->value);

                if (!$input->getOption('generate')->value) {
                    $args = $input->getArguments();
                    $app->runCollector(
                    $args[0],
                    $this->getScanner($input),
                    $input->getOption('public')->value
                    );
                }
                if (!$input->getOption('collect')->value) {
                    $app->runGenerator(
                    $input->getOption('backend')->value,
                    $input->getOption('docs')->value,
                    $input->getOption('public')->value
                    );
                }
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
                fwrite(STDERR, "Error while processing request:\n");
                fwrite(STDERR, ' - ' . $e."\n");
                exit(1);
            }
        }

        /**
         * Helper to load requested require files
         *
         * @param Array $require Array of files to require
         */
        protected function processRequire(Array $require) {
            foreach($require as $file) {
                if (!file_exists($file) || !is_file($file)) {
                    throw new CLIException("Require file '$file' not found or not a file", CLIException::RequireFailed);
                }
                require $file;
            }
        }

        /**
         * Helper to get instance of DirectoryScanner with cli options applied
         *
         * @param ezcConsoleInput $input CLI Options pased to app
         *
         * @return Theseer\Tools\IncludeExcludeFilterIterator
         */
        protected function getScanner(\ezcConsoleInput $input) {
            $scanner = new \TheSeer\Tools\DirectoryScanner;

            $include = $input->getOption('include');
            if (is_array($include->value)) {
                $scanner->setIncludes($include->value);
            } else {
                $scanner->addInclude($include->value);
            }

            $exclude = $input->getOption('exclude');
            if ($exclude->value) {
                if (is_array($exclude->value)) {
                    $scanner->setExcludes($exclude->value);
                } else {
                    $scanner->addExclude($exclude->value);
                }
            }

            $args = $input->getArguments();
            return $scanner($args[0]);
        }


        /**
         * Helper to output version information.
         */
        protected function showVersion() {
            printf("phpdox %s - Copyright (C) 2010 - 2011 by Arne Blankerts\n\n", self::VERSION);
        }

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
            'b', 'backend', \ezcConsoleInput::TYPE_STRING, 'htmlBuilder', false,
            'Transformation/Processing backend to use (default: htmlBuilder)'
            ));
            $input->registerOption( new \ezcConsoleOption(
            'p', 'public', \ezcConsoleInput::TYPE_NONE, null, false,
            'Only show public member and methods'
            ));

            $gen = $input->registerOption( new \ezcConsoleOption(
            'g', 'generate', \ezcConsoleInput::TYPE_NONE, null, false,
            'No collecting, generate documentation only'
            ));
            $col = $input->registerOption( new \ezcConsoleOption(
            'c', 'collect', \ezcConsoleInput::TYPE_NONE, null, false,
            'Only collect data, do not generate docs'
            ));
            $gen->addExclusion(new \ezcConsoleOptionRule($col));
            $col->addExclusion(new \ezcConsoleOptionRule($gen));

            $input->registerOption( new \ezcConsoleOption(
            's', 'silent', \ezcConsoleInput::TYPE_NONE, null, false,
            'Do not output anything to the console'
            ));
            $input->registerOption( new \ezcConsoleOption(
            'l', 'log', \ezcConsoleInput::TYPE_STRING, null, false,
            'Generate XML style logfile'
            ));
            $input->registerOption( new \ezcConsoleOption(
            'r', 'require', \ezcConsoleInput::TYPE_STRING, null, true,
            'Custom PHP Source file to load'
            ));
            $input->argumentDefinition = new \ezcConsoleArguments();
            $input->argumentDefinition[0] = new \ezcConsoleArgument( "directory" );
            $input->argumentDefinition[0]->shorthelp = "The directory to process.";

        }

        /**
         * Helper to output usage information.
         */
        protected function showUsage() {
            print <<<EOF
Usage: phpdox [switches] <directory>

  -x, --xml        Output directory for collected data (default: ./xml)
  -d, --docs       Output directory for generated documentation (default: ./docs)
  -b, --backend    Transformation/Processing backend to use (default: htmlBuilder)

  -p, --public 	 Only process public member and methods

  -c, --collect    Only collect data, do not generate docs
  -g, --generate   No collecting, generate documentation only

  -l, --log        Generate XML style logfile (not implemented yet)
  -s, --silent     Do not output anything to the console (not implemented yet)

  -i, --include    File pattern to include (default: *.php)
  -e, --exclude    File pattern to exclude

  -r, --require    Custom PHP Source file to load

  -h, --help       Prints this usage information
  -v, --version    Prints the version and exits


EOF;
        }

    }

    class CLIException extends \Exception {
        const RequireFailed = 1;
    }

}