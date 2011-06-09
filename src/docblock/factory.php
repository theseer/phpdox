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
namespace TheSeer\phpDox\DocBlock {

    use \TheSeer\phpDox\FactoryInterface;

    class Factory implements FactoryInterface {

        protected $parserMap = array(
            'invalid' => 'TheSeer\\phpDox\\DocBlock\\InvalidParser',
            'generic' => 'TheSeer\\phpDox\\DocBlock\\GenericParser',

            'description' => 'TheSeer\\phpDox\\DocBlock\\DescriptionParser',
            'param' => 'TheSeer\\phpDox\\DocBlock\\ParamParser',
            'var' => 'TheSeer\\phpDox\\DocBlock\\VarParser',
            'return' => 'TheSeer\\phpDox\\DocBlock\\VarParser',
            'license' => 'TheSeer\\phpDox\\DocBlock\\LicenseParser',

            'internal' => 'TheSeer\\phpDox\\DocBlock\\InternalParser'
        );

        protected $elementMap = array(
            'invalid' => 'TheSeer\\phpDox\\DocBlock\\InvalidElement',
            'generic' => 'TheSeer\\phpDox\\DocBlock\\GenericElement'
        );

        public function addParserFactory($annotation, FactoryInterface $factory) {
            $this->parserMap[$annotation] = $factory;
        }

        public function addParserClass($annotation, $class) {
            $this->parserMap[$annotation] = $class;
        }

        public function getInstanceFor($name) {
            switch ($name) {
                case 'DocBlock': {
                    return new DocBlock();
                }
                case 'InlineProcessor': {
                    $args = func_get_args();
                    return new InlineProcessor($this, $args[1]);
                }
                default: {
                    throw new FactoryException("No class defined for name '$name'", FactoryException::UnkownClass);
                }
            }
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
                return $map[$name]->getInstanceOf($name, $this, $annotation);
            }
            return new $map[$name]($this, $annotation);

        }

    }

    class FactoryException extends \Exception {
       const UnkownClass = 1;
    }
}