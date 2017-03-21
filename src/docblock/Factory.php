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
namespace TheSeer\phpDox\DocBlock {

    use TheSeer\fDOM\fDOMDocument;
    use TheSeer\phpDox\FactoryInterface;

    class Factory {

        private $parserMap = array(
            'invalid' => 'TheSeer\\phpDox\\DocBlock\\InvalidParser',
            'generic' => 'TheSeer\\phpDox\\DocBlock\\GenericParser',

            'description' => 'TheSeer\\phpDox\\DocBlock\\DescriptionParser',
            'param' => 'TheSeer\\phpDox\\DocBlock\\ParamParser',
            'var' => 'TheSeer\\phpDox\\DocBlock\\VarParser',
            'return' => 'TheSeer\\phpDox\\DocBlock\\VarParser',
            'throws' => 'TheSeer\\phpDox\\DocBlock\\VarParser',
            'license' => 'TheSeer\\phpDox\\DocBlock\\LicenseParser',

            'internal' => 'TheSeer\\phpDox\\DocBlock\\InternalParser',
            'inheritdoc' => 'TheSeer\\phpDox\\DocBlock\\InheritdocParser'
        );

        private $elementMap = array(
            'inheritdoc' => 'TheSeer\\phpDox\\DocBlock\\InheritdocAttribute',
            'invalid' => 'TheSeer\\phpDox\\DocBlock\\InvalidElement',
            'generic' => 'TheSeer\\phpDox\\DocBlock\\GenericElement',
            'var' => 'TheSeer\\phpDox\\DocBlock\\VarElement'
        );

        /**
         * Register a parser factory.
         *
         * @param string $annotation Identifier of the parser within the registry.
         * @param \TheSeer\phpDox\FactoryInterface|string $factory Instance of FactoryInterface to be registered or FQCN
         *        of the object to be created.
         *
         * @throws FactoryException in case $annotation is not a string.
         */
        public function addParserFactory($annotation, $factory) {
            $this->verifyType($annotation);
            $this->parserMap[$annotation] = $factory;
        }

        /**
         * Register a parser by its classname.
         *
         * @param string $annotation Identifier of the parser within the registry.
         * @param string $classname  Name of the class representing the parser.
         */
        public function addParserClass($annotation, $classname) {
            $this->verifyType($annotation);
            $this->verifyType($classname);
            $this->parserMap[$annotation] = $classname;
        }

        public function getDocBlock() {
            return new DocBlock();
        }

        public function getInlineProcessor(fDOMDocument $dom) {
            return new InlineProcessor($this, $dom);
        }

        public function getElementInstanceFor($name, $annotation = null) {
            return $this->getInstanceByMap($this->elementMap, $name, $annotation);
        }

        public function getParserInstanceFor($name, $annotation = null) {
            return $this->getInstanceByMap($this->parserMap, $name, $annotation);
        }

        protected function getInstanceByMap($map, $name, $annotation = null) {

            if ($annotation === null) {
                $annotation = $name;
            }

            if (!isset($map[$name])) {
                $name = 'generic';
            }

            if ($map[$name] instanceof FactoryInterface) {
                return $map[$name]->getInstanceFor($name, $this, $annotation);
            }
            return new $map[$name]($this, $annotation);

        }

        /**
         * Verify the type of the given item matches the expected one.
         *
         * @param mixed $item
         * @param string $type
         * @throws FactoryException in case the item type and the expected type do not match.
         */
        protected function verifyType($item, $type = 'string') {
            $match = true;
            switch (mb_strtolower($type)) {
                case 'string': {
                    if (!is_string($item)) {
                        $match = false;
                    }
                    break;
                }
                default: {
                    throw new FactoryException('Unknown type chosen for verification', FactoryException::UnknownType);
                    break;
                }
            }

            if (!$match) {
                throw new FactoryException('Argument must be a string.', FactoryException::InvalidType);
            }
        }

    }

}
