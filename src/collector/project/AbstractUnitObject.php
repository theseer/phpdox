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
namespace TheSeer\phpDox\Collector {

    use TheSeer\fDOM\fDOMDocument;
    use TheSeer\fDOM\fDOMElement;
    use TheSeer\phpDox\DocBlock\DocBlock;
    use TheSeer\phpDox\FileInfo;

    /**
     *
     */
    abstract class AbstractUnitObject {

        /**
         * PHPDOX Namespace
         */
        const XMLNS = 'http://xml.phpdox.net/src';

        /**
         * @var fDOMDocument
         */
        private $dom;

        /**
         * @var fDOMElement
         */
        private $rootNode;

        /**
         * @var string
         */
        protected $rootName = NULL;

        /**
         * @param string       $name
         * @param \SplFileInfo $file
         */
        public function __construct($name = NULL, SourceFile $file = NULL) {
            if ($this->rootName === NULL) {
                throw new UnitObjectException('No or invalid rootname set', UnitObjectException::InvalidRootname);
            }
            $this->dom = new fDOMDocument('1.0', 'UTF-8');
            $this->dom->registerNamespace('phpdox', self::XMLNS);
            $this->rootNode = $this->dom->createElementNS(self::XMLNS, $this->rootName);
            $this->dom->appendChild($this->rootNode);
            if ($name !== NULL) {
                $this->setName($name, $this->rootNode);
            }
            if ($file !== NULL) {
                $this->rootNode->appendChild($file->asNode($this->rootNode));
            }
            $this->setAbstract(FALSE);
            $this->setFinal(FALSE);
        }

        /**
         * @param $name
         */
        protected function setName($name, fDOMElement $ctx) {
            $parts = explode('\\', $name);
            $local = array_pop($parts);
            $namespace = join('\\', $parts);
            $ctx->setAttribute('full', $name);
            $ctx->setAttribute('namespace', $namespace);
            $ctx->setAttribute('name', $local);
        }


        protected function getRootNode() {
            return $this->rootNode;
        }

        /**
         * @return \TheSeer\fDOM\fDOMDocument
         */
        public function export() {
            return $this->dom;
        }

        /**
         * @param \TheSeer\fDOM\fDOMDocument $dom
         */
        public function import(fDOMDocument $dom) {
            $this->dom = $dom;
            $this->rootNode = $dom->documentElement;
            $this->dom->registerNamespace('phpdox', self::XMLNS);
        }

        public function getType() {
            return $this->rootNode->localName;
        }

        /**
         * @return string
         */
        public function getLocalName() {
            return $this->rootNode->getAttribute('name');
        }

        /**
         * @return string
         */
        public function getName() {
            return $this->rootNode->getAttribute('full');
        }

        /**
         * @return string
         */
        public function getNamespace() {
            return $this->rootNode->getAttribute('namespace');
        }

        /**
         * @return FileInfo
         */
        public function getSourceFilename() {
            $file = $this->rootNode->queryOne('phpdox:file');
            if (!$file) {
                return '';
            }
            return new FileInfo($file->getAttribute('path') . '/' . $file->getAttribute('file'));
        }

        /**
         * @return string
         */
        public function getCompactDescription() {
            $desc = $this->rootNode->queryOne('phpdox:docblock/phpdox:description');
            if (!$desc || !$desc->hasAttribute('compact')) {
                return '';
            }
            return $desc->getAttribute('compact');
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

        /**
         * @param \TheSeer\phpDox\DocBlock\DocBlock $docblock
         */
        public function setDocBlock(DocBlock $docblock) {
            $docNode = $docblock->asDom($this->dom);
            $this->rootNode->appendChild($docNode);
        }

        /**
         * @param $name
         */
        public function addExtends($name) {
            $extends = $this->rootNode->appendElementNS(self::XMLNS, 'extends');
            $this->setName($name, $extends);
        }

        /**
         * @return bool
         */
        public function hasExtends() {
            return $this->rootNode->queryOne('phpdox:extends') !== NULL;
        }

        /**
         * @return mixed
         * @throws UnitObjectException
         */
        public function getExtends() {
            if(!$this->hasExtends()) {
                throw new UnitObjectException('This unit does not extend any unit', UnitObjectException::NoExtends);
            }
            $result = array();
            foreach($this->rootNode->query('phpdox:extends') as $ext) {
                $result[] = $ext->getAttribute('full');
            }
            return $result;
        }

        /**
         * @param AbstractUnitObject $unit
         */
        public function addExtender(AbstractUnitObject $unit) {
            if ($this->rootNode->queryOne(sprintf('phpdox:extenders/phpdox:*[@full = "%s"]', $unit->getName())) !== NULL) {
                return;
            }
            $extender = $this->addToContainer('extenders', 'extender');
            $this->setName($unit->getName(), $extender);
        }

        /**
         * @param $name
         */
        public function addImplements($name) {
            $implements = $this->rootNode->appendElementNS(self::XMLNS, 'implements');
            $this->setName($name, $implements);
        }

        /**
         * @return bool
         */
        public function hasImplements() {
            return $this->rootNode->query('phpdox:implements')->length > 0;
        }

        /**
         * @return array
         * @throws UnitObjectException
         */
        public function getImplements() {
            if (!$this->hasImplements()) {
                throw new UnitObjectException('This unit does not implement any interfaces', UnitObjectException::NoImplements);
            }
            $result = array();
            foreach($this->rootNode->query('phpdox:implements') as $impl) {
                $result[] = $impl->getAttribute('full');
            }
            return $result;
        }

        /**
         * @return bool
         */
        public function usesTraits() {
            return $this->rootNode->query('phpdox:uses')->length > 0;
        }

        /**
         * @param $name
         *
         * @return bool
         */
        public function usesTtrait($name) {
            return $this->rootNode->query(sprintf('phpdox:uses[@full="%s"]', $name))->length > 0;
        }

        /**
         * @param string $name
         *
         * @return TraitUseObject
         */
        public function addTrait($name) {
            $traituse = new TraitUseObject($this->rootNode->appendElementNS(self::XMLNS, 'uses'));
            $traituse->setName($name);
            return $traituse;
        }

        /**
         * @return array
         * @throws UnitObjectException
         */
        public function getUsedTraits() {
            if (!$this->usesTraits()) {
                throw new UnitObjectException('This unit does not use any traits', UnitObjectException::NoTraitsUsed);
            }
            $result = array();
            foreach($this->rootNode->query('phpdox:uses') as $trait) {
                $result[] = $trait->getAttribute('full');
            }
            return $result;
        }

        /**
         * @param $name
         *
         * @return TraitUseObject
         * @throws UnitObjectException
         */
        public function getTraitUse($name) {
            $node = $this->rootNode->queryOne(
                sprintf('phpdox:uses[@full="%s"]', $name)
            );
            if (!$node) {
                throw new UnitObjectException(
                    sprintf('Trait "%s" not used', $name),
                    UnitObjectException::NoSuchTrait
                );
            }
            return new TraitUseObject($node);
        }

        /**
         * @param string $dependency
         */
        public function markDependencyAsUnresolved($dependency) {
            $depNode = $this->rootNode->queryOne(
                sprintf('//phpdox:implements[@full="%1$s"]|//phpdox:extends[@full="%1$s"]|//phpdox:uses[@full="%1$s"]', $dependency)
            );
            if (!$depNode) {
                throw new UnitObjectException(
                    sprintf('No dependency "%s" found in unit %s', $dependency, $this->getName()),
                    UnitObjectException::NoSuchDependency
                );
            }
            $depNode->setAttribute('unresolved', 'true');
        }

        /**
         *
         */
        public function addMethod($name) {
            switch ($name) {
                case '__construct':
                {
                    $nodeName = 'constructor';
                    break;
                }
                case '__destruct':
                {
                    $nodeName = 'destructor';
                    break;
                }
                default:
                    $nodeName = 'method';
            }
            $method = new MethodObject($this, $this->rootNode->appendElementNS(self::XMLNS, $nodeName));
            $method->setName($name);
            return $method;
        }

        /**
         * @return MethodObject[]
         */
        public function getExportedMethods() {
            $result = array();
            $xpath = '(phpdox:constructor|phpdox:destructor|phpdox:method)[@visibility="public" or @visibility="protected"]';
            foreach($this->rootNode->query($xpath) as $node) {
                $result[] = new MethodObject($this, $node);
            }
            return $result;
        }

        /**
         * @param $name
         *
         * @return MemberObject
         */
        public function addMember($name) {
            $member = new MemberObject($this->rootNode->appendElementNS(self::XMLNS, 'member'));
            $member->setName($name);
            return $member;
        }

        /**
         * @return array
         */
        public function getExportedMembers() {
            $result = array();
            $xpath = 'phpdox:member[@visibility="public" or @visibility="protected"]';
            foreach($this->rootNode->query($xpath) as $node) {
                $result[] = new MemberObject($node);
            }
            return $result;
        }

        /**
         * @param $name
         *
         * @return ConstantObject
         */
        public function addConstant($name) {
            $const = new ConstantObject($this->rootNode->appendElementNS(self::XMLNS, 'constant'));
            $const->setName($name);
            return $const;
        }

        /**
         * @return array
         */
        public function getConstants() {
            $result = array();
            $xpath = 'phpdox:constant';
            foreach($this->rootNode->query($xpath) as $node) {
                $result[] = new ConstantObject($node);
            }
            return $result;
        }

        /**
         * @param AbstractUnitObject $unit
         */
        public function importExports(AbstractUnitObject $unit, $container = 'parent') {

            $parent = $this->rootNode->queryOne(sprintf('//phpdox:%s[@full="%s"]', $container, $unit->getName()));
            if ($parent instanceof fDOMElement) {
                $parent->parentNode->removeChild($parent);
            }

            $parent = $this->rootNode->appendElementNS( self::XMLNS, $container);
            $parent->setAttribute('full', $unit->getName());
            $parent->setAttribute('namespace', $unit->getNamespace());
            $parent->setAttribute('name', $unit->getLocalName());

            if ($unit->hasExtends()) {
                foreach($unit->getExtends() as $name) {
                    $extends = $parent->appendElementNS( self::XMLNS, 'extends');
                    $this->setName($name, $extends);
                }
            }

            if ($unit->hasImplements()) {
                foreach($unit->getImplements() as $name) {
                    $implements = $parent->appendElementNS( self::XMLNS, 'implements');
                    $this->setName($name, $implements);
                }
            }

            if ($unit->usesTraits()) {
                foreach($unit->getUsedTraits() as $name) {
                    $uses = $parent->appendElementNS( self::XMLNS, 'uses');
                    $this->setName($name, $uses);
                }
            }

            foreach($unit->getConstants() as $constant) {
                $parent->appendChild( $this->dom->importNode($constant->export(), TRUE) );
            }

            foreach($unit->getExportedMembers() as $member) {
                $memberNode = $this->dom->importNode($member->export(), TRUE);
                $this->adjustStaticResolution($memberNode);
                $parent->appendChild($memberNode);
            }

            foreach($unit->getExportedMethods() as $method) {
                $methodNode = $this->dom->importNode($method->export(), TRUE);
                $this->adjustStaticResolution($methodNode);
                $parent->appendChild( $methodNode );
                if ($this->hasMethod($method->getName())) {
                    $unitMethod = $this->getMethod($method->getName());
                    if ($unitMethod->hasInheritDoc()) {
                        $unitMethod->inhertDocBlock($method);
                    }
                }
            }
        }

        public function importTraitExports(AbstractUnitObject $trait, TraitUseObject $use) {

            $container = $this->rootNode->queryOne(
                sprintf(
                    'phpdox:trait[@full="%s"]',
                    $trait->getName()
                )
            );
            if ($container instanceof fDOMElement) {
                $container->parentNode->removeChild($container);
            }

            $container = $this->rootNode->appendElementNS( self::XMLNS, 'trait');
            $this->setName($trait->getName(), $container);

            if ($trait->hasExtends()) {
                foreach($trait->getExtends() as $name) {
                    $extends = $container->appendElementNS( self::XMLNS, 'extends');
                    $this->setName($name, $extends);
                }
            }

            if ($trait->usesTraits()) {
                foreach($trait->getUsedTraits() as $name) {
                    $used = $container->appendElementNS( self::XMLNS, 'uses');
                    $this->setName($name, $used);
                }
            }

            foreach($trait->getConstants() as $constant) {
                $container->appendChild( $this->dom->importNode($constant->export(), TRUE) );
            }

            foreach($trait->getExportedMembers() as $member) {
                $memberNode = $this->dom->importNode($member->export(), TRUE);
                $this->adjustStaticResolution($memberNode);
                $container->appendChild($memberNode);
            }

            foreach($trait->getExportedMethods() as $method) {
                $methodName = $method->getName();
                $methodNode = $this->dom->importNode($method->export(), TRUE);

                if (!$use->isExcluded($methodName)) {
                    $container->appendChild($methodNode);
                }

                $this->adjustStaticResolution($methodNode);

                $aliasNode = NULL;
                if ($use->isAliased($methodName)) {
                    $aliasNode = $methodNode->cloneNode(true);
                    $aliasNode->setAttribute('original', $aliasNode->getAttribute('name'));
                    $aliasNode->setAttribute('name', $use->getAliasedName($methodName));
                    if ($use->hasAliasedModifier($methodName)) {
                        $aliasNode->setAttribute('visibility', $use->getAliasedModifier($methodName));
                    }
                    $container->appendChild($aliasNode);
                }
            }

        }

        private function hasMethod($name) {
            return $this->dom->query(
                sprintf('phpdox:method[@name="%s"]', $name)
            )->length > 0;
        }

        private function getMethod($name) {
            $ctx = $this->dom->queryOne(
                sprintf('phpdox:method[@name="%s"]', $name)
            );
            if (!$ctx) {
                throw new UnitObjectException(
                    sprintf('Method "%s" not found', $name),
                    UnitObjectException::NoSuchMethod
                );
            }
            return new MethodObject($this, $ctx);
        }

        private function adjustStaticResolution(fDOMElement $ctx) {
            $container = $ctx->queryOne('.//phpdox:docblock/phpdox:return|.//phpdox:docblock/phpdox:var');
            if (!$container || $container->getAttribute('resolution') !== 'static') {
                return;
            }
            $type = $container->queryOne('phpdox:type');
            if (!$type) {
                return;
            }
            foreach(array('full','namespace','name') as $attribute) {
                $type->setAttribute($attribute, $this->rootNode->getAttribute($attribute));
            }
        }

        /**
         * @param $containerName
         * @param $elementName
         *
         * @return fDOMElement
         */
        protected function addToContainer($containerName, $elementName) {
            $container = $this->rootNode->queryOne('phpdox:' . $containerName);
            if (!$container) {
                $container = $this->rootNode->appendElementNS(self::XMLNS, $containerName);
            }
            return $container->appendElementNS(self::XMLNS, $elementName);
        }

    }

}
