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

    use \TheSeer\fDom\fDomDocument;

    class Container {

        /**
         * Base path xml files are stored in
         *
         * @var string
         */
        protected $xmlDir;

        /**
         * Array of fDOMDocuments
         *
         * @var array
         */
        protected $documents = array();


        public function __construct($xmlDir) {
            $this->xmlDir = $xmlDir;
        }

        /**
         * Helper to save all known and (updated) container files.
         */
        public function save() {
            foreach($this->documents as $fname => $dom) {
                $dom->save($fname);
            }
        }

        /**
         * Helper to load or create Container DOM Documents for namespaces, classes, interfaces, ...
         *
         * @param string $name name of the file (identical to root node)
         *
         * @return \TheSeer\fDom\fDomDocument
         */
        public function getDocument($name) {
            $fname = $this->xmlDir . '/' . $name .'.xml';
            if (isset($this->documents[$fname])) {
                return $this->documents[$fname];
            }
            $dom = new fDOMDocument('1.0', 'UTF-8');
            if (file_exists($fname)) {
                $dom->load($fname);
            } else {
                $rootNode = $dom->createElementNS('http://xml.phpdox.de/src#', $name);
                $dom->appendChild($rootNode);
            }
            $dom->registerNamespace('phpdox', 'http://xml.phpdox.de/src#');
            $dom->formatOutput = true;
            $this->documents[$fname] = $dom;
            return $dom;
        }

    }
}