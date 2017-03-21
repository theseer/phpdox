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

    use TheSeer\fDOM\fDOMDocument;
    use TheSeer\fDOM\fDOMException;

    class ConfigLoader {

        const XMLNS = 'http://xml.phpdox.net/config';

        /**
         * @var FileInfo
         */
        private $homeDir;

        /**
         * @var Version
         */
        private $version;

        /**
         * @param FileInfo $homeDir
         */
        public function __construct(Version $version, FileInfo $homeDir) {
            $this->version = $version;
            $this->homeDir = $homeDir;
        }

        /**
         * @param $fname
         *
         * @return GlobalConfig
         * @throws ConfigLoaderException
         */
        public function load($fname) {
           if (!file_exists($fname)) {
               throw new ConfigLoaderException("Config file '$fname' not found", ConfigLoaderException::NotFound);
           }
           return $this->createInstanceFor($fname);
        }

        /**
         * @return GlobalConfig
         * @throws ConfigLoaderException
         */
        public function autodetect() {
            $candidates = array(
                    './phpdox.xml',
                    './phpdox.xml.dist'
            );
            foreach($candidates as $fname) {
                if (!file_exists($fname)) {
                    continue;
                }
                return $this->createInstanceFor($fname);
            }
            throw new ConfigLoaderException("None of the candidate files found", ConfigLoaderException::NeitherCandidateExists);
        }

        /**
         * @param $fname
         *
         * @return GlobalConfig
         * @throws ConfigLoaderException
         */
        private function createInstanceFor($fname) {
            $dom = $this->loadFile($fname);
            $this->ensureCorrectNamespace($dom);
            $this->ensureCorrectRootNodeName($dom);
            return new GlobalConfig($this->version, $this->homeDir, $dom, new FileInfo($fname));
        }

        /**
         * @param $fname
         *
         * @return fDOMDocument
         * @throws ConfigLoaderException
         */
        private function loadFile($fname) {
            try {
                $dom = new fDOMDocument();
                $dom->load($fname);
                $dom->registerNamespace('cfg', self::XMLNS);
                return $dom;
            } catch (fDOMException $e) {
                throw new ConfigLoaderException(
                    "Parsing config file '$fname' failed.",
                    ConfigLoaderException::ParseError,
                    $e
                );
            }
        }

        /**
         * @param fDOMDocument $dom
         *
         * @throws ConfigLoaderException
         */
        private function ensureCorrectNamespace(fDOMDocument $dom) {
            if ($dom->documentElement->namespaceURI != self::XMLNS) {
                throw new ConfigLoaderException(
                    sprintf(
                        "The configuratin file '%s' uses a wrong or outdated xml namespace.\n" .
                        "Please ensure it uses 'http://xml.phpdox.net/config'",
                        $dom->documentURI
                    ), ConfigLoaderException::WrongNamespace
                );
            }
        }

        /**
         * @param fDOMDocument $dom
         *
         * @throws ConfigLoaderException
         */
        private function ensureCorrectRootNodeName(fDOMDocument $dom) {
            if ($dom->documentElement->localName != 'phpdox') {
                throw new ConfigLoaderException(
                    sprintf(
                        "The file '%s' does not seem to be a phpdox configration file.",
                        $dom->documentURI
                    ), ConfigLoaderException::WrongType
                );
            }
        }

    }

}
