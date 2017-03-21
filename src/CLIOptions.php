<?php
    /**
     * Copyright (c) 2010-2017 Arne Blankerts <arne@blankerts.de>
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
     */

namespace TheSeer\phpDox {

    class CLIOptions {

        private $argv;
        private $parsed;

        public function __construct(array $argv) {
            $this->argv = $argv;
        }

        public function getHelpScreen() {
            return <<<EOF
Usage: phpdox [switches]

  -f, --file       Configuration file to use (defaults to ./phpdox.xml[.dist])

  -h, --help       Prints this usage information
  -v, --version    Prints the version and exits

  -c, --collector  Run only collector process
  -g, --generator  Run only generator process

      --backends   Show a list of available backends and exit
      --engines    Show a list of available output engines and exit
      --enrichers  Show a list of available output enrichers and exit

      --skel       Show an annotated skeleton config xml file and exit
      --strip      Strip comments from skeleton config xml when showing


EOF;

        }

        public function showHelp() {
            $this->parse();
            return $this->parsed['help'];
        }

        public function showVersion() {
            $this->parse();
            return $this->parsed['version'];
        }

        public function listBackends() {
            $this->parse();
            return $this->parsed['backends'];
        }

        public function listEngines() {
            $this->parse();
            return $this->parsed['engines'];
        }

        public function listEnrichers() {
            $this->parse();
            return $this->parsed['enrichers'];
        }

        public function generateSkel() {
            $this->parse();
            return $this->parsed['skel'];
        }

        public function generateStrippedSkel() {
            $this->parse();
            return $this->parsed['strip'];
        }

        public function configFile() {
            $this->parse();
            if (!is_string($this->parsed['file'])) {
                return '';
            }
            return $this->parsed['file'];
        }

        public function generatorOnly() {
            $this->parse();
            return $this->parsed['generator'];
        }

        public function collectorOnly() {
            $this->parse();
            return $this->parsed['collector'];
        }

        private function parse() {
            $options = array(
                'file','help','version','collector','generator', 'engines', 'enrichers', 'backends', 'skel', 'strip'
            );
            $shortMap = array(
                'f' => 'file',
                'h' => 'help',
                'c' => 'collector',
                'g' => 'generator',
                'v' => 'version'
            );
            $valueOptions = array(
                'file'
            );

            $conflictingOptions = array(
                'collector' => array('generator'),
                'generator' => array('collector')
            );

            $argv = $this->argv;
            array_map('trim', $argv);

            if (isset($argv[0][0]) && $argv[0][0] != '-') {
                array_shift($argv);
            }

            foreach($options as $opt) {
                $this->parsed[$opt] = FALSE;
            }

            $valueExcepted = false;
            $argName = '';
            foreach($argv as $arg) {
                if ($arg[0] == '-') {
                    if (strlen($arg) == 1) {
                        throw new CLIOptionsException(
                            sprintf('Syntax error while parsing option (unnamed switch or option)')
                        );
                    }
                    if ($arg[1] == '-') {
                        $argName = mb_substr($arg, 2);
                        if (!in_array($argName, $options)) {
                            throw new CLIOptionsException(
                                sprintf('Option "%s" is not defined', $argName)
                            );
                        }
                    } else {
                        $argChar = mb_substr($arg, 1);
                        if (!isset($shortMap[$argChar])) {
                            throw new CLIOptionsException(
                                sprintf('Option "%s" is not defined', $argChar)
                            );
                        }
                        $argName = $shortMap[$argChar];
                    }
                    if (isset($conflictingOptions[$argName])) {
                        foreach($conflictingOptions[$argName] as $conflict) {
                            if ($this->parsed[$conflict]) {
                                throw new CLIOptionsException(
                                    sprintf('Option "%s" conflicts with already set option "%s"', $argName, $conflict)
                                );
                            }
                        }
                    }
                    $this->parsed[$argName] = TRUE;
                    $valueExcepted = in_array($argName, $valueOptions);
                    continue;
                }
                if (!$valueExcepted) {
                    throw new CLIOptionsException(
                        sprintf('Value for option "%s" provided but none expected', $argName)
                    );
                }
                $this->parsed[$argName] = $arg;
            }

        }

    }

}
