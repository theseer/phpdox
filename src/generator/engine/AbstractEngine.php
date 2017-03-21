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

namespace TheSeer\phpDox\Generator\Engine {

    use TheSeer\fDOM\fDOMDocument;
    use TheSeer\fXSL\fXSLTProcessor;
    use TheSeer\phpDox\DirectoryCleaner;
    use TheSeer\phpDox\FileInfo;

    abstract class AbstractEngine implements EngineInterface {

        protected function getXSLTProcessor($template) {
            $tpl = new fDomDocument();
            $tpl->load($template);
            if (stripos(PHP_OS, 'Linux') !== 0) {
                $this->resolveImports($tpl);
            }
            return new fXSLTProcessor($tpl);
        }

        protected function clearDirectory($path) {
            $cleaner = new DirectoryCleaner();
            $cleaner->process(new FileInfo($path));
        }

        protected function saveDomDocument(\DOMDocument $dom, $filename, $format = true) {
            $path = dirname($filename);
            clearstatcache();
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            $dom->formatOutput = $format;
            return $dom->save($filename);
        }

        protected function saveFile($content, $filename) {
            $path = dirname($filename);
            clearstatcache();
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            return file_put_contents($filename, $content);
        }

        protected function copyStatic($path, $dest, $recursive = true) {
            $len  = mb_strlen($path);
            if ($recursive) {
                $worker = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
            } else {
                $worker = new \DirectoryIterator($path);
            }
            foreach($worker as $x) {
                if($x->isDir() && ($x->getFilename() == "." || $x->getFilename() == "..")) {
                    continue;
                }
                $target = $dest . mb_substr($x->getPathname(), $len);
                if (!file_exists(dirname($target))) {
                    mkdir(dirname($target), 0777, true);
                }
                copy($x->getPathname(), $target);
            }
        }

        private function resolveImports(fDOMDocument $doc) {
            $doc->registerNamespace('xsl', 'http://www.w3.org/1999/XSL/Transform');
            $baseDir = dirname($doc->documentURI);
            $baseElement = $doc->documentElement;
            foreach($doc->query('/xsl:stylesheet/xsl:import') as $importNode) {
                /** @var $importNode \DOMElement */
                $import = new fDOMDocument();
                $import->load($baseDir . '/' . $importNode->getAttribute('href'));

                $newParent = $importNode->parentNode;
                foreach ($import->documentElement->childNodes as $child) {
                    if ($child->localName === 'output') {
                        continue;
                    }
                    $importedChild = $doc->importNode($child, true);
                    $newParent->insertBefore($importedChild, $importNode);
                }
                $newParent->removeChild($importNode);
            }
        }

    }

    class EngineException extends \Exception {
        const UnexpectedError = 1;
    }

}
