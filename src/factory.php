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
 */
namespace TheSeer\phpDox {

    use \TheSeer\fDOM\fDOMElement;

    class Factory {

        protected $map = array(
            'DirectoryScanner' => '\\TheSeer\\Tools\\DirectoryScanner'
        );

        protected $instances = array();
        protected $xmlDir;

        public function __construct(array $map = null) {
            if ($map !== null) {
                $this->map = $map;
            }
        }

        public function setXMLDir($path) {
            $this->xmlDir = $path;
        }

        public function getInstanceFor($name) {
            if (!isset($this->map[$name])) {
                throw new FactoryException("No class for '$name' in map found", FactoryException::NoClassDefined);
            }
            return new $this->map[$name]();
        }

        public function getAnalyser($public) {
            return new Analyser($this, $public);
        }

        public function getLogger($name) {
            switch ($name) {
                case 'silent': {
                    return new ProgressLogger();
                }
                case 'shell': {
                    return new ShellProgressLogger();
                }
            }
        }

        public function getApplication() {
            return new Application($this, $this->getContainer(), $this->xmlDir);
        }

        public function getContainer() {
            if (!isset($this->instances['container'])) {
                $this->instances['container'] = new Container($this->xmlDir);
            }
            return $this->instances['container'];

        }

        public function getScanner($include, $exclude) {
            $scanner = $this->getInstanceFor('DirectoryScanner');

            if (is_array($include)) {
                $scanner->setIncludes($include);
            } else {
                $scanner->addInclude($include);
            }

            if ($exclude != null) {
                if (is_array($exclude)) {
                    $scanner->setExcludes($exclude);
                } else {
                    $scanner->addExclude($exclude);
                }
            }
            return $scanner;
        }

        public function getCollector() {
            return new Collector($this, $this->getContainer(), $this->xmlDir);
        }

        public function getClassBuilder(fDOMElement $ctx, $public, $encoding) {
            return new ClassBuilder($this->getDocblockParser(), $ctx, $public, $encoding);
        }

        protected function getDocblockParser() {
            if (!isset($this->instances['parser'])) {
                $this->instances['parser'] = new \TheSeer\phpDox\DocBlock\Parser();
            }
            return $this->instances['parser'];
        }

    }

    class FactoryException extends \Exception {
        const NoClassDefined = 1;
    }

}