<?php
    /**
     * Copyright (c) 2010-2014 Arne Blankerts <arne@blankerts.de>
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
namespace TheSeer\phpDox\Collector {

    use TheSeer\phpDox\FileInfo;
    use TheSeer\fDOM\fDOMDocument;
    use TheSeer\fDOM\fDOMElement;

    class SourceCollection implements DOMCollectionInterface {

        /**
         * @var FileInfo
         */
        private $srcDir;

        private $original = array();
        private $collection = array();

        private $workDom;

        public function __construct($srcDir) {
            $this->srcDir = $srcDir;
            $this->workDom = new fDOMDocument();
            $this->workDom->registerNamespace('phpdox', 'http://xml.phpdox.net/src#');
        }

        public function import(fDOMDocument $dom) {
            $dom->registerNamespace('phpdox', 'http://xml.phpdox.net/src#');
            $dir = $dom->queryOne('/phpdox:source/phpdox:dir');
            if (!$dir)  {
                return;
            }
            $this->importDirNode($dir, '');
        }

        public function addFile(FileInfo $file) {
            $node = $this->workDom->createElementNS('http://xml.phpdox.net/src#', 'file');
            $node->setAttribute('name', basename($file->getBasename()));
            $node->setAttribute('size', $file->getSize());
            $node->setAttribute('time', date('c', $file->getMTime()));
            $node->setAttribute('unixtime', $file->getMTime());
            $node->setAttribute('sha1', sha1_file($file->getPathname()));

            $relPath = (string)$file->getRelative($this->srcDir);
            $this->collection[$relPath] = $node;
            return $this->isChanged($relPath);
        }

        public function removeFile(FileInfo $file) {
            $relPath = (string)$file->getRelative($this->srcDir);
            unset($this->collection[$relPath]);
        }

        public function getChangedFiles() {
            $list = array();
            foreach(array_keys($this->collection) as $path) {
                if ($this->isChanged($path)) {
                    $list[] = $path;
                }
            }
            return $list;
        }

        public function getVanishedFiles() {
            $list = array();
            foreach(array_keys($this->original) as $path) {
                if (!isset($this->collection[$path])) {
                    $list[] = $path;
                }
            }
            return $list;
        }

        public function export() {
            $dom = $this->workDom;
            if (sizeof($this->collection) === 0) {
                if (!$dom->documentElement instanceof fDOMElement) {
                    $root = $dom->createElementNS('http://xml.phpdox.net/src#', 'source');
                    $dom->appendChild($root);
                }
                return $this->workDom;
            }
            if ($dom->documentElement instanceOf fDOMElement) {
                $dom->removeChild($dom->documentElement);
            }
            $root = $dom->createElementNS('http://xml.phpdox.net/src#', 'source');
            $this->workDom->appendChild($root);
            foreach($this->collection as $path => $file) {
                $dirs = explode('/', dirname($path));
                $ctx = $root;
                foreach($dirs as $dir) {
                    $node = $ctx->queryOne('phpdox:dir[@name="'. $dir . '"]');
                    if (!$node) {
                        $node = $ctx->appendElementNS('http://xml.phpdox.net/src#', 'dir');
                        $node->setAttribute('name', $dir);
                    }
                    $ctx = $node;
                }
                $ctx->appendChild($this->workDom->importNode($file, true));
            }

            $this->collection = array();
            return $dom;
        }


        private function importDirNode(fDOMElement $dir, $path) {
            $path .=  $dir->getAttribute('name');
            foreach($dir->query('phpdox:file') as $file) {
                $this->original[ $path . '/' . $file->getAttribute('name')] = $file;
            }
            foreach($dir->query('phpdox:dir') as $dir) {
                $this->importDirNode($dir, $path . '/');
            }
        }

        private function isChanged($path) {
            if (!isset($this->original[$path])) {
                return true;
            }
            $org = $this->original[$path];
            $new = $this->collection[$path];
            return $org->getAttribute('sha1') != $new->getAttribute('sha1');
        }

    }

}