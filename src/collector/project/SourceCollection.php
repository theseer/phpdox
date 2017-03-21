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
namespace TheSeer\phpDox\Collector {

    use TheSeer\phpDox\FileInfo;
    use TheSeer\fDOM\fDOMDocument;
    use TheSeer\fDOM\fDOMElement;

    class SourceCollection {

        /**
         * @var FileInfo
         */
        private $srcDir;

        /**
         * @var fDOMElement[]
         */
        private $original = array();

        /**
         * @var fDOMElement[]
         */
        private $collection = array();

        private $workDom;

        public function __construct(FileInfo $srcDir) {
            $this->srcDir = $srcDir;
            $this->workDom = new fDOMDocument();
            $this->workDom->registerNamespace('phpdox', 'http://xml.phpdox.net/src');
            $this->workDom->appendElementNS('http://xml.phpdox.net/src', 'source');
        }

        public function import(fDOMDocument $dom) {
            $dom->registerNamespace('phpdox', 'http://xml.phpdox.net/src');
            $dir = $dom->queryOne('/phpdox:source/phpdox:dir');
            if (!$dir)  {
                return;
            }
            $this->importDirNode($dir, '');
        }

        public function addFile(SourceFile $file) {
            $path = $file->getRealPath();
            $node = $this->workDom->createElementNS('http://xml.phpdox.net/src', 'file');
            $node->setAttribute('name', basename($file->getBasename()));
            $node->setAttribute('size', $file->getSize());
            $node->setAttribute('time', date('c', $file->getMTime()));
            $node->setAttribute('unixtime', $file->getMTime());
            $node->setAttribute('sha1', sha1_file($file->getPathname()));
            $this->collection[$path] = $node;
            $changed = $this->isChanged($path);
            if (!$changed) {
                $node->setAttribute('xml', $this->original[$path]->getAttribute('xml'));
            }
            return $changed;
        }

        public function setTokenFileReference(SourceFile $file, $tokenPath) {
            $path = $file->getRealPath();
            if (!isset($this->collection[$path])) {
                throw new SourceCollectionException(
                    sprintf("File %s not found in collection", $path),
                    SourceCollectionException::SourceNotFound
                );
            }
            $this->collection[$path]->setAttribute('xml', $tokenPath);
        }

        public function removeFile(FileInfo $file) {
            if (!isset($this->collection[$file->getRealPath()])) {
                throw new SourceCollectionException(
                    sprintf("File %s not found in collection", $file->getRealPath()),
                    SourceCollectionException::SourceNotFound
                );
            }
            unset($this->collection[$file->getRealPath()]);
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

        public function export($collapse = false) {
            if (count($this->collection) == 0) {
                return $this->workDom;
            }

            $root = $this->workDom->documentElement;
            while($root->hasChildNodes()) {
                $root->nodeValue = null;
            }

            foreach ($this->collection as $path => $file) {
                $pathInfo = new FileInfo($path);
                $dirs = explode('/', dirname($pathInfo->getRelative($this->srcDir)));
                $dirs[0] = $this->srcDir->getRealPath();
                $ctx = $root;
                foreach ($dirs as $dir) {
                    $node = $ctx->queryOne('phpdox:dir[@name="' . $dir . '"]');
                    if (!$node) {
                        $node = $ctx->appendElementNS('http://xml.phpdox.net/src', 'dir');
                        $node->setAttribute('name', $dir);
                    }
                    $ctx = $node;
                }
                $ctx->appendChild($this->workDom->importNode($file, TRUE));
            }

            $this->collection = array();

            if ($collapse) {
                $this->collapseDirectory();
            }
            return $this->workDom;
        }

        private function importDirNode(fDOMElement $dir, $path) {
            $path .=  $dir->getAttribute('name');
            foreach($dir->query('phpdox:file') as $file) {
                $this->original[ $path . '/' . $file->getAttribute('name')] = $file;
            }
            foreach($dir->query('phpdox:dir') as $child) {
                $this->importDirNode($child, $path . '/');
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

        private function collapseDirectory() {
            $first = $this->workDom->queryOne('/phpdox:source/phpdox:dir');
            if ($first->query('phpdox:file')->length == 0 &&
                $first->query('phpdox:dir')->length == 1) {
                $dir = $first->queryOne('phpdox:dir');
                foreach($dir->query('*') as $child) {
                    $first->appendChild($child);
                }
                $first->setAttribute('name', $first->getAttribute('name') . '/' . $dir->getAttribute('name'));
                $first->removeChild($dir);
                $this->collapseDirectory();
            }
        }

    }

}
