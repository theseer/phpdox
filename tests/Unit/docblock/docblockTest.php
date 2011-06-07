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

    use TheSeer\phpDox\DocBlock\GenericElement;
    use TheSeer\phpDox\DocBlock\DocBlock;

    class DocBlockTest extends \PHPUnit_Framework_TestCase {


        /*********************************************************************/
        /* Fixtures                                                          */
        /*********************************************************************/

        /**
         * Provides a fixture representing an instance of the class 'GenericElement'.
         *
         * @param string $name
         * @param array $methods
         *
         * @return TheSeer\phpDox\DocBlock\GenericElement
         */
        protected function getGenericElementFixture($name, array $methods) {
            return $this->getMockBuilder('TheSeer\phpDox\DocBlock\GenericElement')
                ->setConstructorArgs(array($name))
                ->setMethods($methods)
                ->getMock();
        }

        /*********************************************************************/
        /* Tests                                                             */
        /*********************************************************************/

        /**
         * @covers TheSeer\phpDox\DocBlock\DocBlock::appendElement
         */
        public function testAppendElement() {

            $docBlock = new DocBlock();

            $genericElement = $this->getGenericElementFixture('Beastie', array('getAnnotationName'));
            $genericElement
                ->expects($this->once())
                ->method('getAnnotationName')
                ->will($this->returnValue('Beastie'));

            $docBlock->appendElement($genericElement);

            $elements = $this->readAttribute($docBlock, 'elements');
            $this->assertArrayHasKey('Beastie', $elements);
            $this->assertInstanceOf('TheSeer\phpDox\DocBlock\GenericElement', $elements['Beastie']);
        }

        /**
         * @covers TheSeer\phpDox\DocBlock\DocBlock::appendElement
         */
        public function testAppendElementExistingElement() {
            $genericElement = $this->getGenericElementFixture('Beastie', array('getAnnotationName'));
            $genericElement
                ->expects($this->exactly(3))
                ->method('getAnnotationName')
                ->will($this->returnValue('Beastie'));

            $docBlock = new DocBlock();
            $docBlock->appendElement($genericElement);
            $docBlock->appendElement($genericElement);
            $docBlock->appendElement($genericElement);

            $elements = $this->readAttribute($docBlock, 'elements');
            $this->assertArrayHasKey('Beastie', $elements);
            $this->assertInternalType('array', $elements['Beastie']);
            $this->assertEquals(3, count($elements['Beastie']));
        }

        /**
         * @dataProvider hasElementByNameDataprovider
         * @covers TheSeer\phpDox\DocBlock\DocBlock::hasElementByName
         */
        public function testHasElementByName($expected, $name, $elements) {
            $docBlock = new DocBlockProxy();
            $docBlock->elements = $elements;
            $this->assertEquals($expected, $docBlock->hasElementByName($name));
        }

        /**
         * @covers TheSeer\phpDox\DocBlock\DocBlock::getElementByName
         */
        public function testGetElementByName() {
            $docBlock = new DocBlockProxy();
            $docBlock->elements = array('Tux' => true);
            $this->assertTrue($docBlock->getElementByName('Tux'));
        }

        /**
         * @expectedException TheSeer\phpDox\DocBlock\DocBlockException
         * @covers TheSeer\phpDox\DocBlock\DocBlock::getElementByName
         */
        public function testGetElementByNameExpectingDocBlockException() {
            $docBlock = new DocBlockProxy();
            $docBlock->elements = array('Tux' => true);
            $docBlock->getElementByName('Gnu');
        }

        /**
         * @covers TheSeer\phpDox\DocBlock\DocBlock::asDom
         */
        public function testAsDomNoRegisteredElments() {

            $node = new \stdClass;

            $fDomDocument = $this->getMockBuilder('TheSeer\fDOM\fDOMDocument')
                ->disableOriginalConstructor()
                ->setMethods(array('createElementNS'))
                ->getMock();
            $fDomDocument
                ->expects($this->once())
                ->method('createElementNS')
                ->will($this->returnValue($node));

            $docBlock = new DocBlockProxy();
            $this->assertEquals(new \stdClass, $docBlock->asDom($fDomDocument));

        }

        /*********************************************************************/
        /* Dataprovider                                                      */
        /*********************************************************************/

        public static function hasElementByNameDataprovider() {
            return array(
                'known element' => array(true, 'Tux', array('Tux' => true)),
                'unknown element' => array(false, 'Gnu', array('Tux' => true)),
            );
        }
    }


    class DocBlockProxy extends DocBlock {
        public $elements = array();
    }
}