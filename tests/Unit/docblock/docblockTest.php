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
 * @author     Arne Blankerts <arne@blankerts.de>
 * @copyright  Arne Blankerts <arne@blankerts.de>, All rights reserved.
 * @license    BSD License
 */
namespace TheSeer\phpDox\DocBlock {

    use TheSeer\fDOM\fDOMDocument;

    /**
     * Class DocBlockTest
     *
     * @covers TheSeer\phpDox\DocBlock\DocBlock
     * @uses TheSeer\phpDox\DocBlock\GenericElement
     */
    class DocBlockTest extends \PHPUnit\Framework\TestCase {

        /**
         * @var DocBlock
         */
        private $docBlock;

        /**
         * @var GenericElement
         */
        private $element;

        protected function setUp() {
            $this->docBlock = new DocBlock();

            $this->element = $this->getMockBuilder('TheSeer\\phpDox\\DocBlock\\GenericElement')
                ->disableOriginalConstructor()
                ->getMock();

            $this->element->expects($this->any())
                            ->method('getAnnotationName')
                            ->will($this->returnValue('stub'));
        }

        public function testHasElementByNameReturnsFalseIfNotPresent() {
            $this->assertFalse($this->docBlock->hasElementByName('not-set'));
        }

        /**
         * @covers TheSeer\phpDox\DocBlock\DocBlock::appendElement
         * @covers TheSeer\phpDox\DocBlock\DocBlock::hasElementByName
         */
        public function testElementCanBeAdded() {
            $this->docBlock->appendElement($this->element);
            $this->assertTrue($this->docBlock->hasElementByName('stub'));
        }

        /**
         * @covers TheSeer\phpDox\DocBlock\DocBlock::appendElement
         */
        public function testSameTypeElementCanBeAddedMultipleTimes() {
            $this->docBlock->appendElement($this->element);
            $this->docBlock->appendElement($this->element);
            $this->assertTrue($this->docBlock->hasElementByName('stub'));
            $this->assertCount(2, $this->docBlock->getElementByName('stub'));
        }

        /**
         * @expectedException \TheSeer\phpDox\DocBlock\DocBlockException
         * @covers TheSeer\phpDox\DocBlock\DocBlock::getElementByName
         */
        public function testTryingToGetANonExistingElementThrowsException() {
            $this->docBlock->getElementByName('non-set');
        }


        /**
         * @covers TheSeer\phpDox\DocBlock\DocBlock::getElementByName
         */
        public function testElementCanBeRetreived() {
            $this->docBlock->appendElement($this->element);
            $this->assertEquals($this->element, $this->docBlock->getElementByName('stub'));
        }

        public function testDocBlockCanBeSerializedToDom() {
            $dom = new fDOMDocument();
            $dom->registerNamespace('test', 'http://xml.phpdox.net/src');
            $this->element->expects($this->once())
                ->method('asDom')
                ->will($this->returnValue($dom->createElementNS('http://xml.phpdox.net/src', 'stub')));

            $this->docBlock->appendElement($this->element);
            $node = $this->docBlock->asDom($dom);

            $this->assertEquals(
                '<docblock xmlns="http://xml.phpdox.net/src"><stub/></docblock>',
                $dom->saveXML($node)
            );
        }

        public function testDocBlockWithMultipleOccurencesOfAnnotationCanBeSerializedToDom() {
            $dom = new fDOMDocument();
            $dom->registerNamespace('test', 'http://xml.phpdox.net/src');

            $element2 = clone $this->element;
            $this->element->expects($this->once())
                ->method('asDom')
                ->will($this->returnValue($dom->createElementNS('http://xml.phpdox.net/src', 'stub')));

            $element2->expects($this->once())
                ->method('asDom')
                ->will($this->returnValue($dom->createElementNS('http://xml.phpdox.net/src', 'stub')));

            $this->docBlock->appendElement($this->element);
            $this->docBlock->appendElement($element2);

            $node = $this->docBlock->asDom($dom);

            $this->assertEquals(
                '<docblock xmlns="http://xml.phpdox.net/src"><stub/><stub/></docblock>',
                $dom->saveXML($node)
            );
        }
    }

}
