<?php
/**
 * Copyright (c) 2010-2015 Arne Blankerts <arne@blankerts.de>
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
 * @subpackage Tests
 * @author     Bastian Feder <phpdox@bastian-feder.de>
 * @copyright  Arne Blankerts <arne@blankerts.de>, All rights reserved.
 * @license    BSD License
 */

namespace TheSeer\phpDox\Tests\Integration\DocBlock {

    use TheSeer\phpDox\DocBlock\Factory;
    use TheSeer\phpDox\FactoryInterface;

    /**
     * Class FactoryTest
     *
     * @covers TheSeer\phpDox\DocBlock\Factory
     */
    class FactoryTest extends \PHPUnit\Framework\TestCase {

        /**
         * @covers TheSeer\phpDox\DocBlock\Factory::getElementInstanceFor
         * @uses TheSeer\phpDox\DocBlock\GenericElement
         */
        public function testGetElementInstanceFor() {
            $factory = new Factory();
            $this->assertInstanceOf(
                'TheSeer\\phpDox\\DocBlock\\GenericElement',
                $factory->getElementInstanceFor('Tux')
            );
        }

        /**
         * @covers TheSeer\phpDox\DocBlock\Factory::getParserInstanceFor
         * @uses TheSeer\phpDox\DocBlock\GenericParser
         */
        public function testGetParserInstanceFor() {
            $factory = new Factory();
            $this->assertInstanceOf(
                'TheSeer\\phpDox\\DocBlock\\GenericParser',
                $factory->getParserInstanceFor('Tux')
            );
        }

        /**
         * @dataProvider getInstanceMapDataprovider
         * @covers TheSeer\phpDox\DocBlock\Factory::getInstanceByMap
         *
         * @uses TheSeer\phpDox\Tests\Integration\DocBlock\FactoryProxy
         * @uses TheSeer\fDOM\fDOMDocument
         * @uses TheSeer\fDOM\fDOMElement
         *
         * @uses TheSeer\phpDox\DocBlock\GenericElement
         * @uses TheSeer\phpDox\DocBlock\InvalidElement
         * @uses TheSeer\phpDox\DocBlock\InvalidParser
         * @uses TheSeer\phpDox\DocBlock\GenericParser
         * @uses TheSeer\phpDox\DocBlock\DescriptionParser
         * @uses TheSeer\phpDox\DocBlock\ParamParser
         * @uses TheSeer\phpDox\DocBlock\VarParser
         * @uses TheSeer\phpDox\DocBlock\VarParser
         * @uses TheSeer\phpDox\DocBlock\LicenseParser
         * @uses TheSeer\phpDox\DocBlock\InternalParser
         */
        public function testGetInstanceByMap($expected, $name, $elementMap) {
            $factory = new FactoryProxy();
            $this->assertInstanceOf(
                $expected,
                $factory->getInstanceByMap($elementMap, $name)
            );
        }

        /**
         * @covers TheSeer\phpDox\DocBlock\Factory::getInstanceByMap
         * @uses TheSeer\phpDox\Tests\Integration\DocBlock\FactoryProxy
         */
        public function testGetInstanceByMapHandlingAFactory() {

            $factoryMock = $this->createMock(FactoryInterface::class);
            $factoryMock
                ->expects($this->once())
                ->method('getInstanceFor')
                ->will($this->returnValue(new \stdClass));

            $factory = new FactoryProxy();

            $this->assertInstanceOf(
                '\stdClass',
                $factory->getInstanceByMap(array('GnuFactory' => $factoryMock), 'GnuFactory')
            );
        }

        /*********************************************************************/
        /* Dataprovider                                                      */
        /*********************************************************************/

        public static function getInstanceMapDataprovider() {
            $elementMap = array(
                'invalid' => 'TheSeer\\phpDox\\DocBlock\\InvalidElement',
                'generic' => 'TheSeer\\phpDox\\DocBlock\\GenericElement'
            );

            $parserMap = array(
                'invalid' => 'TheSeer\\phpDox\\DocBlock\\InvalidParser',
                'generic' => 'TheSeer\\phpDox\\DocBlock\\GenericParser',

                'description' => 'TheSeer\\phpDox\\DocBlock\\DescriptionParser',
                'param' => 'TheSeer\\phpDox\\DocBlock\\ParamParser',
                'var' => 'TheSeer\\phpDox\\DocBlock\\VarParser',
                'return' => 'TheSeer\\phpDox\\DocBlock\\VarParser',
                'license' => 'TheSeer\\phpDox\\DocBlock\\LicenseParser',

                'internal' => 'TheSeer\\phpDox\\DocBlock\\InternalParser'
            );
            return array(
                'GenericElement by name from elementMap' => array (
                    'TheSeer\\phpDox\\DocBlock\\GenericElement',
                    'generic',
                    $elementMap
                ),
                'GenericElement by unkown name from elementMap' => array (
                    'TheSeer\\phpDox\\DocBlock\\GenericElement',
                    'Tux',
                    $elementMap
                ),
                'InvalidElement by name from elementMap' => array (
                    'TheSeer\\phpDox\\DocBlock\\InvalidElement',
                    'invalid',
                    $elementMap
                ),
                'InvalidParser by name from parserMap' => array (
                    'TheSeer\\phpDox\\DocBlock\\InvalidParser',
                    'invalid',
                    $parserMap
                ),
                'GenericParser by unkown name from parserMap' => array (
                    'TheSeer\\phpDox\\DocBlock\\GenericParser',
                    'Tux',
                    $parserMap
                ),
            );
        }
    }

    class FactoryProxy extends Factory {
        public function getInstanceByMap($map, $name, $annotation = null) {
            return parent::getInstanceByMap($map, $name, $annotation);
        }
    }

}
