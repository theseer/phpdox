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
 */
namespace TheSeer\phpDox {

    use \TheSeer\fDOM\fDOMDocument;
    use \TheSeer\fXSL\fXSLTProcessor;

    class Generator {
        protected $xmlDir;
        protected $docDir;

        protected $publicOnly = false;

        protected $namespaces;
        protected $interfaces;
        protected $classes;

        /**
         * Generator constructor
         *
         * @param string $xmlDir      Base path where class xml files are found
         * @param string $docDir      Base directory to store documentation files in
         * @param fDomDocument $nsDom DOM instance to register namespaces in
         * @param fDomDocument $iDom  DOM instance to register interfaces in
         * @param fDomDocument $cDom  DOM instance to register classes in
         */
        public function __construct($xmlDir, $docDir, fDOMDocument $nsDom, fDOMDocument $iDom, fDOMDocument $cDom) {
            $this->xmlDir  = $xmlDir;
            $this->docDir  = $docDir;

            $this->namespaces = $nsDom;
            $this->interfaces = $iDom;
            $this->classes    = $cDom;
        }

        public function setPublicOnly($switch) {
            $this->publicOnly = $switch;
        }

        public function isPublicOnly() {
            return $this->publicOnly;
        }

        public function getNamespacesAsDOM() {
            return $this->namespaces;
        }

        public function getInterfacesAsDOM() {
            return $this->interfaces;
        }

        public function getClassesAsDOM() {
            return $this->classes;
        }

        public function getXMLDirectory() {
            return $this->xmlDir;
        }

        public function getDocumentationDirectory() {
            return $this->docDir;
        }

        /**
         * Main executer of the generator
         *
         * @param string $class Classname of the backend implementation to use
         */
        public function run($class) {
            if (strpos('\\', $class)===false) {
                $class = 'TheSeer\\phpDox\\' . $class;
            }

            if (!class_exists($class, true)) {
                throw new GeneratorException("Backend class '$class' is not defined", GeneratorException::ClassNotDefined);
            }
            $backend = new $class();
            if (!$backend instanceof genericBackend) {
                throw new GeneratorException("'$class' must implement the GeneratorBackendInterface to be used as backend", GeneratorException::UnsupportedBackend);
            }
            $backend->run($this);
        }

    }

    class GeneratorException extends \Exception {
        const ClassNotDefined    = 1;
        const UnsupportedBackend = 2;
        const UnexepctedType     = 3;
    }
}