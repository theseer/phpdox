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
 *
 */
namespace TheSeer\phpDox {

    use \TheSeer\fDOM\fDOMDocument;
    use \TheSeer\fDOM\fDOMElement;

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

        public function getWorkDir() {
            return $this->xmlDir;
        }

        /**
         * Helper to save all known and (updated) container files.
         */
        public function save() {
            foreach($this->documents as $fname => $dom) {
                $dom->formatOutput = true;
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
            $dom->preserveWhiteSpace = false;
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

        public function needsUpdate($srcIndex, \SplFileInfo $file) {
            $dom = $this->getDocument('source');
            $path = dirname(substr($file->getPathname(),$srcIndex));
            $ctx = $dom->documentElement;
            foreach(explode('/', $path) as $dir) {
                $d = $ctx->queryOne('phpdox:dir[@name="'.$dir.'"]');
                if (!$d) {
                    return true;
                }
                $ctx = $d;
            }
            $f = $ctx->queryOne('phpdox:file[@name="' . $file->getBasename() . '"]');
            if (!$f) {
                return true;
            }
            return $file->getCTime() != $f->getAttribute('unixtime');
        }

        public function registerFile($srcIndex, \SplFileInfo $file) {
            $dom = $this->getDocument('source');
            $path = dirname(substr($file->getPathname(),$srcIndex));
            $ctx = $dom->documentElement;
            foreach(explode('/', $path) as $dir) {
                if ($dir=='') {
                    continue;
                }
                $d = $ctx->queryOne('phpdox:dir[@name="'.$dir.'"]');
                if (!$d) {
                   $d = $ctx->appendElementNS('http://xml.phpdox.de/src#', 'dir');
                   $d->setAttribute('name', $dir);
                }
                $ctx = $d;
            }
            $f = $ctx->queryOne('phpdox:file[@name="' . $file->getBasename() . '"]');
            if (!$f) {
                $f = $ctx->appendElementNS('http://xml.phpdox.de/src#', 'file');
                $f->setAttribute('name',  $file->getBasename());
            }
            $update = $file->getCTime() != $f->getAttribute('unixtime');
            if ($update) {
                $f->setAttribute('size', $file->getSize());
                $f->setAttribute('time', date('c', $file->getCTime()));
                $f->setAttribute('unixtime', $file->getCTime());
                $f->setAttribute('sha1', sha1_file($file->getPathname()));
            }
            return $update;
        }

        public function registerUnit(fDOMDocument $dom, $fname) {
            $ctx = $dom->queryOne('//phpdox:class|//phpdox:interface|//phpdox:trait');
            $node2name = array(
                'class' => 'classes',
                'interface' => 'interfaces',
                'trait' => 'traits',
            );
            $container = $this->getDocument($node2name[$ctx->localName]);
            $head = $dom->queryOne('//phpdox:head');

            $namespace = $ctx->getAttribute('namespace');
            $target = $container->queryOne('//phpdox:namespace[@name="' . $namespace . '"]');
            if (!$target) {
                $target = $container->documentElement->appendElementNS('http://xml.phpdox.de/src#','namespace');
                $target->setAttribute('name', $namespace);
            }

            $workNode = $target->queryOne('.//phpdox:' . $ctx->localName . '[@full="' . $ctx->getAttribute('full') . '"]');
            if ($workNode) {
                $workNode->parentNode->removeChild($workNode);
            }

            $workNode = $target->appendElementNS('http://xml.phpdox.de/src#', $ctx->localName);
            foreach($ctx->attributes as $attr) {
                $workNode->appendChild($container->importNode($attr, true));
            }
            $workNode->setAttribute('xml', substr($fname, strlen($this->xmlDir)+1));
            $workNode->setAttribute('src', $head->getAttribute('path').'/'.$head->getAttribute('file'));
            foreach($ctx->query('.//phpdox:implements|.//phpdox:extends') as $node) {
                $workNode->appendChild($container->importNode($node, true));
            }

            $this->registerNamespaces($namespace, $workNode->getAttribute('src'), $workNode->getAttribute('xml'));
        }

        public function cleanup($srcDir) {
            $ctx = $this->getDocument('source')->queryOne('/phpdox:source/phpdox:dir[1]');
            $base = dirname($srcDir);
            $this->checkDir(strlen($base)+1, $base, $ctx);
        }

        protected function checkDir($offset, $srcDir, fDOMElement $dir) {
            $path = $srcDir . '/' . $dir->getAttribute('name');
            foreach($dir->query('phpdox:file') as $file) {
                $fname = $path . '/' . $file->getAttribute('name');
                if (!file_exists( $fname )) {
                    $this->removeFile(substr($fname, $offset));
                    $dir->removeChild($file);
                }
            }
            foreach($dir->query('phpdox:dir') as $sub) {
                $this->checkDir($offset, $path, $sub);
            }
            if (!$dir->hasChildNodes()) {
                $dir->parentNode->removeChild($dir);
            }
        }

        protected function registerNamespaces($namespace, $src, $xml) {
            $nsDoc = $this->getDocument('namespaces');
            $nsNode = $nsDoc->queryOne("//phpdox:namespace[@name='$namespace']");
            if (!$nsNode) {
                $nsNode = $nsDoc->documentElement->appendElementNS('http://xml.phpdox.de/src#', 'namespace');
                $nsNode->setAttribute('name', $namespace);
            }
            $fNode = $nsNode->queryOne("./phpdox:file[@src='$src']");
            if (!$fNode) {
                $file = $nsNode->appendElementNS('http://xml.phpdox.de/src#', 'file');
                $file->setAttribute('src', $src);
                $file->setAttribute('xml', $xml);
            }
        }

        protected function removeFile($fname) {
            foreach(array('classes','interfaces','traits','namespaces') as $name) {
                $dom = $this->getDocument($name);
                $list = $dom->query('//*[@src="'.$fname.'"]');
                foreach($list as $node) {
                    $xml = $this->xmlDir . '/' . $node->getAttribute('xml');
                    if (file_exists($xml)) {
                        unlink($xml);
                    }
                    $parent = $node->parentNode;
                    $parent->removeChild($node);
                    if ($parent->localName == 'namespace' && !$parent->hasChildNodes()) {
                        $parent->parentNode->removeChild($parent);
                    }
                }
            }
        }

    }
}