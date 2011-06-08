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
 * @subpackage unitTests
 * @author     Bastian Feder <phpdox@bastina-feder.de>
 * @copyright  Arne Blankerts <arne@blankerts.de>, All rights reserved.
 * @license    BSD License
 */

namespace TheSeer\phpDox\Tests\Unit\DocBlock {

    use TheSeer\phpDox\DocBlock\Factory;

    class FactoryTest extends \PHPUnit_Framework_TestCase {

        /**
         * @covers TheSeer\phpDox\DocBlock\Factory::addParserFactory
         */
        public function testAddParserFactory() {
            $factory = new Factory();
            $factory->addParserFactory('Tux', $factory);
            $this->assertAttributeContains($factory, 'parserMap', $factory);
        }

        /**
         * @expectedException TheSeer\phpDox\DocBlock\FactoryException
         * @covers TheSeer\phpDox\DocBlock\Factory::addParserFactory
         */
        public function testAddParserFactoryExpectingFactoryException() {
            $factory = new Factory();
            $factory->addParserFactory(array(), $factory);
        }

        /**
         * @covers TheSeer\phpDox\DocBlock\Factory::addParserClass
         */
        public function testAddParserClass() {
            $factory = new Factory();
            $factory->addParserClass('Tux', 'Gnu');
            $this->assertAttributeContains('Gnu', 'parserMap', $factory);
        }

        /**
         * @dataProvider addParserClassDataprovider
         * @expectedException TheSeer\phpDox\DocBlock\FactoryException
         * @covers TheSeer\phpDox\DocBlock\Factory::addParserClass
         */
        public function testAddParserClassExpectingFactoryException($annotation, $classname) {
            $factory = new Factory();
            $factory->addParserClass($annotation, $classname);
        }

        /**
         * @dataProvider getInstanceForClassDataprovider
         * @covers TheSeer\phpDox\DocBlock\Factory::getInstanceFor
         */
        public function testGetInstanceFor($expected, $name, $annotation) {
            $factory = new Factory();
            $this->assertInstanceOf($expected, $factory->getInstanceFor($name, $annotation));
        }

        /**
         * @covers TheSeer\phpDox\DocBlock\Factory::getInstanceFor
         */
        public function testGetInstanceForRegisteredFactory() {
            $factory = new Factory();
            $factory->addParserFactory('exception', new Factory());

            $this->assertInstanceOf('TheSeer\\phpDox\\DocBlock\\GenericParser', $factory->getInstanceFor('exception'));
        }

        /**
         * @covers TheSeer\phpDox\DocBlock\Factory::verifyType
         */
        public function testVerifyType() {
            $factory = new FactoryProxy();
            $this->assertNull($factory->verifyType('Tux'));
        }

        /**
         * @expectedException TheSeer\phpDox\DocBlock\FactoryException
         * @dataProvider verifyTypeClassDataprovider
         * @covers TheSeer\phpDox\DocBlock\Factory::verifyType
         */
        public function testVerifyTypeExpectingFactoryException($item, $type) {
            $factory = new FactoryProxy();
            $factory->verifyType($item, $type);
        }

        /*********************************************************************/
        /* Dataprovider                                                      */
        /*********************************************************************/

        public static function verifyTypeClassDataprovider() {
            return array(
                'Invalid type' => array(42, 'string'),
                'unknown type' => array(42, 'integer'),
            );
        }

        public static function getInstanceForClassDataprovider() {
            return array(
                'get instance of DocBlock' => array(
                    'TheSeer\\phpDox\\DocBlock\\DocBlock', 'docblock', null
                ),
                'get instance of InvalidParser' => array(
                    'TheSeer\\phpDox\\DocBlock\\InvalidParser', 'invalid', null
                ),
                'get instance of GenericParser by unregistered  name' => array(
                    'TheSeer\\phpDox\\DocBlock\\GenericParser', 'unregistered', null
                ),
                'get instance of GenericParser' => array(
                    'TheSeer\\phpDox\\DocBlock\\GenericParser', 'generic', null
                ),
            );
        }

        public static function addParserClassDataprovider() {
            return array(
                'wrong annotation type' => array(array(), 'Gnu'),
                'wrong classname type' => array('Tux', array()),
            );
        }
    }

    class FactoryProxy extends Factory {

        public function verifyType($item, $type = 'string') {
            parent::verifyType($item, $type);
        }
    }
}