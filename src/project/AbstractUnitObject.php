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
    use TheSeer\fDOM\fDOMElement;
    use TheSeer\phpDox\DocBlock\DocBlock;

    /**
     *
     */
    abstract class AbstractUnitObject {

        const XMLNS = 'http://xml.phpdox.de/src#';

        /**
         * @var fDOMDocument
         */
        private $dom;


        /** @var fDOMElement */
        private $rootNode;

        /**
         * @var string
         */
        protected $rootName = NULL;

        /**
         * @param \SplFileInfo $file
         * @param string       $name
         */
        public function __construct(\SplFileInfo $file, $name) {
            if ($this->rootName === NULL) {
                throw new UnitObjectException('No or invalid rootname set', UnitObjectException::InvalidRootname);
            }
            $this->dom = new fDOMDocument();
            $this->dom->registerNamespace('phpdox', self::XMLNS);
            $this->rootNode = $this->dom->createElementNS(self::XMLNS, $this->rootName);
            $this->dom->appendChild($this->rootNode);
            $this->setFileHeader($file);
            $this->setName($name);
            $this->setAbstract(false);
            $this->setFinal(false);
        }

        private function setFileHeader(\SplFileInfo $file) {
            $fileNode = $this->rootNode->appendElementNS(self::XMLNS, 'file');
            $fileNode->setAttribute('path', $file->getPath());
            $fileNode->setAttribute('file', $file->getBasename());
            $fileNode->setAttribute('realpath', $file->getRealPath());
            $fileNode->setAttribute('size', $file->getSize());
            $fileNode->setAttribute('time', date('c',$file->getMTime()));
            $fileNode->setAttribute('unixtime', $file->getMTime());
            $fileNode->setAttribute('sha1', sha1_file($file->getRealPath()));

        }

        private function setName($name) {
            $parts = explode('\\', $name);
            $local = array_pop($parts);
            $namespace = join('\\', $parts);
            $this->rootNode->setAttribute('full', $name);
            $this->rootNode->setAttribute('namespace', $namespace);
            $this->rootNode->setAttribute('name', $local);
        }

        public function getName() {
            return $this->rootNode->getAttribute('full');
        }

        /**
         * @param int $endLine
         */
        public function setEndLine($endLine) {
            $this->rootNode->setAttribute('end', $endLine);
        }

        /**
         * @param int $startLine
         */
        public function setStartLine($startLine) {
            $this->rootNode->setAttribute('start', $startLine);
        }

        /**
         * @param boolean $isAbstract
         */
        public function setAbstract($isAbstract) {
            $this->rootNode->setAttribute('abstract', $isAbstract ? 'true' : 'false');
        }

        /**
         * @param boolean $isFinal
         */
        public function setFinal($isFinal) {
            $this->rootNode->setAttribute('final', $isFinal ? 'true' : 'false');
        }

        public function setDocBlock(DocBlock $docblock) {
            $docNode = $docblock->asDom($this->dom);
            $this->rootNode->appendChild($docNode);
        }

        public function setExtends($name) {
            $extends = $this->rootNode->queryOne('phpdox:extends');
            if (!$extends) {
                $extends = $this->rootNode->appendElementNS(self::XMLNS, 'extends');
            }
            $extends->setAttribute('full', $name);
            $parts = explode('\\', $name);
            $local = array_pop($parts);
            $extends->setAttribute('class', $local);
            $extends->setAttribute('namespace', join('\\', $parts));
        }

        public function addImplements($name) {
            $implements = $this->rootNode->appendElementNS(self::XMLNS, 'implements');
            $implements->setAttribute('full', $name);
            $parts = explode('\\', $name);
            $local = array_pop($parts);
            $implements->setAttribute('class', $local);
            $implements->setAttribute('namespace', join('\\', $parts));
        }

        /**
         *
         */
        public function addMethod($name) {
            $method = new MethodObject($this->rootNode->appendElementNS(self::XMLNS, 'method'));
            $method->setName($name);
            return $method;
        }

        public function addMember($name) {
            $member = new MemberObject($this->rootNode->appendElementNS(self::XMLNS, 'member'));
            $member->setName($name);
            return $member;
        }

        public function addConstant($name) {
            $const = new ConstantObject($this->rootNode->appendElementNS(self::XMLNS, 'constant'));
            $const->setName($name);
            return $const;
        }

        public function export($xmlDir) {
            if (!file_exists($xmlDir)) {
                mkdir($xmlDir, 0755, true);
            }
            $fileName = $xmlDir . '/' . str_replace('\\', '_', $this->rootNode->getAttribute('full')) . '.xml';
            $this->dom->formatOutput = true;
            $this->dom->preserveWhiteSpace = false;
            $this->dom->save( $fileName);

            $export = $this->rootNode->cloneNode();
            $export->setAttribute('xml', basename($xmlDir) . '/' . basename($fileName));
            if ($extends = $this->rootNode->queryOne('phpdox:extends')) {
                $export->appendChild($extends->cloneNode());
            }
            return $export;
        }

    }

    class UnitObjectException extends \Exception {
        const InvalidRootname = 1;
    }


}