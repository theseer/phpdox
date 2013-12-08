<?php
/**
 * Copyright (c) 2010-2013 Arne Blankerts <arne@blankerts.de>
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

    class ConfigLoader {

        public function load($fname) {
           if (!file_exists($fname)) {
               throw new ConfigLoaderException("Config file '$fname' not found", ConfigLoaderException::NotFound);
           }
           return $this->createInstanceFor($fname);
        }

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
            throw new ConfigLoaderException("None of the candidate files found", ConfigLoaderException::NoCandidateExists);
        }

        protected function createInstanceFor($fname) {
            try {
                $dom = new fDOMDocument();
                $dom->load($fname);

                $root = $dom->documentElement;
                if ($root->namespaceURI == 'http://phpdox.de/config') {
                    throw new ConfigLoaderException("File '$fname' uses an outdated xml namespace. Please update the xmlns to 'http://phpdox.net/config'", ConfigLoaderException::OldNamespace);
                }

                if ($root->namespaceURI != 'http://phpdox.net/config' ||
                    $root->localName != 'phpdox') {
                    throw new ConfigLoaderException("File '$fname' is not a valid phpDox configuration.", ConfigLoaderException::WrongType);
                }
                $dom->registerNamespace('cfg', 'http://phpdox.net/config');

                return new GlobalConfig($dom, realpath($fname));
            } catch (fDOMException $e) {
                throw new ConfigLoaderException("Parsing config file '$fname' failed.", ConfigLoaderException::ParseError, $e);
            }
        }
    }

    class ConfigLoaderException extends \Exception {
        const NotFound = 1;
        const ParseError = 2;
        const NoCandidateExists = 3;
        const WrongType = 4;
        const OldNamespace = 5;
    }
}
