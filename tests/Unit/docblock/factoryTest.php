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

namespace TheSeer\phpDox\Tests\Unit\DocBlock {

    use TheSeer\fDOM\fDOMDocument;
    use TheSeer\phpDox\DocBlock\Factory;
    use TheSeer\phpDox\FactoryInterface;

    /**
     * Class FactoryTest
     *
     * @covers TheSeer\phpDox\DocBlock\Factory
     */
    class FactoryTest extends \PHPUnit\Framework\TestCase {

        private $factory;

        protected function setUp() {
            $this->factory = new Factory();
        }

        /**
         * @covers TheSeer\phpDox\DocBlock\Factory::addParserFactory
         */
        public function testAddParserFactory() {
            $mock = $this->createMock(FactoryInterface::class);
            $this->factory->addParserFactory('Tux', $mock);
            $this->assertAttributeContains($mock, 'parserMap', $this->factory);
        }

        /**
         * @expectedException \TheSeer\phpDox\DocBlock\FactoryException
         * @covers TheSeer\phpDox\DocBlock\Factory::addParserFactory
         */
        public function testAddParserFactoryExpectingFactoryException() {
            $mock = $this->createMock(FactoryInterface::class);
            $this->factory->addParserFactory(array(), $mock);
        }

        /**
         * @covers TheSeer\phpDox\DocBlock\Factory::addParserClass
         */
        public function testAddParserClass() {
            $this->factory->addParserClass('Tux', 'Gnu');
            $this->assertAttributeContains('Gnu', 'parserMap', $this->factory);
        }

        /**
         * @dataProvider addParserClassDataprovider
         * @expectedException \TheSeer\phpDox\DocBlock\FactoryException
         * @covers TheSeer\phpDox\DocBlock\Factory::addParserClass
         */
        public function testAddParserClassExpectingFactoryException($annotation, $classname) {
            $this->factory->addParserClass($annotation, $classname);
        }

        /**
         * @covers TheSeer\phpDox\DocBlock\Factory::getDocBlock
         * @uses TheSeer\phpDox\DocBlock\DocBlock
         */
        public function testGetInstanceForDocBlock() {
            $factory = new Factory();
            $this->assertInstanceOf(
                'TheSeer\\phpDox\\DocBlock\\DocBlock',
                $factory->getDocBlock()
            );
        }

        /**
         * @covers TheSeer\phpDox\DocBlock\Factory::getInlineProcessor
         * @uses TheSeer\phpDox\DocBlock\InlineProcessor
         */
        public function testGetInstanceForInlineProcessor() {
            $factory = new Factory();
            $this->assertInstanceOf(
                'TheSeer\\phpDox\\DocBlock\\InlineProcessor',
                $factory->getInlineProcessor(new fDOMDocument()));
        }

        /**
         * @covers TheSeer\phpDox\DocBlock\Factory::getParserInstanceFor
         * @uses TheSeer\phpDox\DocBlock\GenericParser
         */
        public function testGetParserInstanceForUnknownNameReturnsGenericParser() {
            $factory = new Factory();
            $this->assertInstanceOf(
                'TheSeer\\phpDox\\DocBlock\\GenericParser',
                $factory->getParserInstanceFor('Unknown Name Parser')
            );
        }

        /*********************************************************************/
        /* Dataprovider                                                      */
        /*********************************************************************/

        public static function addParserClassDataprovider() {
            return array(
                'wrong annotation type' => array(array(), 'Gnu'),
                'wrong classname type' => array('Tux', array()),
            );
        }
    }

}
