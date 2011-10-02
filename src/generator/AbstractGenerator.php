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

    use \TheSeer\fXSL\fXSLTProcessor;
    use \TheSeer\fXSL\fXSLCallback;

    use \TheSeer\fDom\fDomDocument;
    use \TheSeer\fDom\fDomElement;

    abstract class AbstractGenerator {

        protected $factory;
        protected $logger;

        protected $xmlDir;
        protected $docDir;
        protected $tplDir;

        protected $publicOnly = false;

        protected $namespaces;
        protected $interfaces;
        protected $classes;


        /**
         * Generator constructor
         *
         * @param Factory   $factroy   Instance of Factory
         * @param string    $xmlDir    Base path where class xml files are found
         * @param string    $tplDir    Base path for templates
         * @param string    $docDir    Base directory to store documentation files in
         * @param Container $container Collection of Container Documents
         */
        public function __construct(Factory $factory, $xmlDir, $tplDir, $docDir, Container $container) {
            $this->factory = $factory;

            $this->xmlDir = $xmlDir;
            $this->docDir = $docDir;
            $this->tplDir = $tplDir;

            $this->namespaces = $container->getDocument('namespaces');
            $this->interfaces = $container->getDocument('interfaces');
            $this->classes    = $container->getDocument('classes');
        }

        public function setPublicOnly($switch) {
            $this->publicOnly = $switch;
        }

        public function getXMLDirectory() {
            return $this->xmlDir;
        }

        public function getOutputDirectory() {
            return $this->docDir;
        }

        public function getTemplateDirectory() {
            return $this->tplDir;
        }

        /**
         * Main executer of the generator
         *
         * @param ProgressLogger $logger
         */
        abstract public function run(array $builderMap, ProgressLogger $logger);

        public function getXSLTProcessor($filename) {
            $tpl = new fDomDocument();
            $tpl->load($this->tplDir . '/' . $filename);
            $xsl = new fXSLTProcessor($tpl);

            $service = new fXSLCallback('phpdox:service','ps');
            $service->setObject($this->factory->getInstanceFor('Service', $this));
            $xsl->registerCallback($service);

            return $xsl;
        }

        public function saveDomDocument(\DOMDocument $dom, $filename) {
            $filename = $this->docDir . '/' . $filename;
            $path = dirname($filename);
            clearstatcache();
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            return $dom->save($filename);
        }

        public function saveFile($content, $filename) {
            $filename = $this->docDir . '/' . $filename;
            $path = dirname($filename);
            clearstatcache();
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            return file_put_contents($filename, $content);
        }

        public function copyStatic($mask, $recursive = true) {
            $path = $this->tplDir . '/' . $mask;
            $len  = strlen($path);
            if ($recursive) {
                $worker = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
            } else {
                $worker = new \DirectoryIterator($path);
            }
            foreach($worker as $x) {
                if($x->isDir() && ($x->getFilename() == "." || $x->getFilename() == "..")) {
                    continue;
                }
                $target = $this->docDir . substr($x->getPathname(), $len);
                if (!file_exists(dirname($target))) {
                    mkdir(dirname($target), 0755, true);
                }
                copy($x->getPathname(), $target);
            }
        }

        public function loadDataFile($filename) {
            $classDom = new fDomDocument();
            $classDom->load($this->xmlDir . '/' . $filename);
            $classDom->registerNamespace('phpdox', 'http://xml.phpdox.de/src#');
            return $classDom;
        }

    }

    class GeneratorException extends \Exception {
        const UnknownEvent = 1;
        const AlreadyRegistered = 2;
    }

}
