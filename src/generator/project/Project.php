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
 */
namespace TheSeer\phpDox\Generator {

    use DOMNodeList;
    use TheSeer\fDOM\fDOMDocument;
    use TheSeer\phpDox\FileInfo;

    /**
     *
     */
    class Project {

        /**
         * @var string
         */
        private $xmlDir;

        /**
         * @var string
         */
        private $srcDir;

        /**
         * @var fDOMDocument
         */
        private $source = NULL;

        /**
         * @var fDOMDocument
         */
        private $index = NULL;

        /**
         * @param FileInfo $srcDir
         * @param FileInfo $xmlDir
         */
        public function __construct(FileInfo $srcDir, FileInfo $xmlDir) {
            $this->xmlDir = $xmlDir;
            $this->srcDir = $srcDir;
            $this->initCollections();
        }

        /**
         * @return FileInfo
         */
        public function getSourceDir() {
            return $this->srcDir;
        }

        /**
         * @return FileInfo
         */
        public function getXmlDir() {
            return $this->xmlDir;
        }


        /**
         * @return Index
         */
        public function getIndex() {
            return new Index($this->index);
        }

        /**
         * @return SourceTree
         */
        public function getSourceTree() {
            return new SourceTree($this->source);
        }

        /**
         * @return bool
         */
        public function hasNamespaces() {
            return $this->index->query('count(//phpdox:namespace[not(@name="/")])') > 0;
        }

        /**
         * @return DOMNodeList
         */
        public function getNamespaces() {
            return new NamespaceCollection($this->index->query('//phpdox:namespace'));
        }

        /**
         * @param string $namespace
         * @return ClassCollection
         */
        public function getClasses($namespace = NULL) {
            $root = ($namespace !== NULL) ? sprintf('//phpdox:namespace[@name="%s"]/', $namespace) : '//';
            return new ClassCollection($this->index->query($root . 'phpdox:class'));
        }

        /**
         * @param string $namespace
         * @return TraitCollection
         */
        public function getTraits($namespace = NULL) {
            $root = ($namespace !== NULL) ? sprintf('//phpdox:namespace[@name="%s"]/', $namespace) : '//';
            return new TraitCollection($this->index->query($root . 'phpdox:trait'));
        }

        /**
         * @param string $namespace
         * @return InterfaceCollection
         */
        public function getInterfaces($namespace = NULL) {
            $root = ($namespace !== NULL) ? sprintf('//phpdox:namespace[@name="%s"]/', $namespace) : '//';
            return new InterfaceCollection($this->index->query($root . 'phpdox:interface'));
        }

        /**
         * @return void
         */
        private function initCollections() {
            $this->source = new fDOMDocument();
            $this->source->load($this->xmlDir . '/source.xml');
            $this->source->registerNamespace('phpdox', 'http://xml.phpdox.net/src');

            $this->index = new fDOMDocument();
            $this->index->load($this->xmlDir . '/index.xml');
            $this->index->registerNamespace('phpdox', 'http://xml.phpdox.net/src');
        }

    }


    class ProjectException extends \Exception {

        const UnitNotFoundInIndex = 1;

    }
}
