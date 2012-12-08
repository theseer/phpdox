<?php
/**
 * Copyright (c) 2010-2012 Arne Blankerts <arne@blankerts.de>
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
namespace TheSeer\phpDox\Project {

    use TheSeer\fDOM\fDOMDocument;

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
         * @var SourceCollection
         */
        private $source = NULL;

        /**
         * @var IndexCollection
         */
        private $index = NULL;

        /**
         * @param $srcDir
         * @param $xmlDir
         */
        public function __construct($srcDir, $xmlDir) {
            $this->xmlDir = $xmlDir;
            $this->srcDir = $srcDir;
            $this->initCollections();
        }

        /**
         * @return string
         */
        public function getSourceDir() {
            return $this->srcDir;
        }

        /**
         * @return string
         */
        public function getXmlDir() {
            return $this->xmlDir;
        }

        /**
         * @param \SplFileInfo $file
         * @return bool
         */
        public function addFile(\SplFileInfo $file) {
            $isNew = $this->source->addFile($file);
            if ($isNew) {
                $this->removeFileReferences($file->getPathname());
            }
            return $isNew;
        }

        /**
         * @param ClassObject $class
         */
        public function addClass(ClassObject $class) {
            $this->index->addClass($class);
        }

        /**
         *
         */
        public function addInterface(InterfaceObject $interface) {
            $this->index->addInterface($interface);
        }

        /**
         *
         */
        public function addTrait(TraitObject $trait) {
            $this->index->addTrait($trait);
        }

        public function getIndex() {
            return $this->index->export();
        }

        public function getSourceTree() {
            return $this->source->export();
        }

        public function hasNamespaces() {
            return $this->index->export()->query('count(//phpdox:namespace[not(@name="/")])') > 0;
        }

        public function getNamespaces() {
            return $this->index->export()->query('//phpdox:namespace');
        }

        public function getClasses($namespace = NULL) {
            $root = ($namespace !== NULL) ? sprintf('//phpdox:namespace[@name="%s"]/', $namespace) : '//';
            return $this->index->export()->query($root . 'phpdox:class');
        }

        public function getTraits($namespace = NULL) {
            $root = ($namespace !== NULL) ? sprintf('//phpdox:namespace[@name="%s"]/', $namespace) : '//';
            return $this->index->export()->query($root . 'phpdox:trait');
        }

        public function getInterfaces($namespace = NULL) {
            $root = ($namespace !== NULL) ? sprintf('//phpdox:namespace[@name="%s"]/', $namespace) : '//';
            return $this->index->export()->query($root . 'phpdox:interface');
        }

        /**
         * @return integer
         */
        public function cleanVanishedFiles() {
            $files = $this->source->getVanishedFiles();
            foreach ($files as $path) {
                $this->removeFileReferences($path);
            }
            return count($files);
        }

        /**
         *
         */
        public function save() {
            $map = array('class' => 'classes', 'trait' => 'traits', 'interface' => 'interfaces');
            foreach ($map as $col) {
                $path = $this->xmlDir . '/' . $col;
                if (!file_exists($path)) {
                    mkdir($path, 0755, TRUE);
                }
            }
            $indexDom = $this->index->export();
            $newUnits = $this->index->getAddedUnits();
            foreach($newUnits as $unit) {
                $name = str_replace('\\', '_', $unit->getFullName());
                $dom = $unit->export();
                $dom->formatOutput = TRUE;
                $dom->preserveWhiteSpace = FALSE;
                $fname = $map[$dom->documentElement->localName] . '/' . $name . '.xml';
                $dom->save($this->xmlDir . '/' . $fname);

                $indexDom->queryOne(sprintf('//phpdox:namespace[@name="%s"]/*[@name="%s"]',
                        $unit->getNamespace(),
                        $unit->getName())
                    )->setAttribute('xml', $fname);
            }
            $indexDom->formatOutput = TRUE;
            $indexDom->preserveWhiteSpace = FALSE;
            $indexDom->save($this->xmlDir . '/index.xml');

            $sourceDom = $this->source->export();
            $sourceDom->formatOutput = TRUE;
            $sourceDom->preserveWhiteSpace = FALSE;
            $sourceDom->save($this->xmlDir . '/source.xml');
        }

        /**
         * @return void
         */
        private function initCollections() {
            $this->source = new SourceCollection($this->srcDir);
            $srcFile = $this->xmlDir . '/source.xml';
            if (file_exists($srcFile)) {
                $dom = new fDOMDocument();
                $dom->load($srcFile);
                $this->source->import($dom);
            }

            $this->index = new IndexCollection();
            $srcFile = $this->xmlDir . '/index.xml';
            if (file_exists($srcFile)) {
                $dom = new fDOMDocument();
                $dom->load($srcFile);
                $this->index->import($dom);
            }

        }

        /**
         * @param string $path
         */
        private function removeFileReferences($path) {
            // Iterate over c/i/t collections, remove unit files, remove entries
        }

    }

}
