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
 * @subpackage Tests
 * @author     Bastian Feder <phpdox@bastian-feder.de>
 * @copyright  Arne Blankerts <arne@blankerts.de>, All rights reserved.
 * @license    BSD License
 */

namespace TheSeer\phpDox\Tests\Integration\DocBlock {

    use TheSeer\phpDox\DocBlock\GenericElement;
    use TheSeer\phpDox\DocBlock\DocBlock;

    class DocBlockTest extends \TheSeer\phpDox\Tests\phpDox_TestCase {

        /**
         * Contains a DOMDocument
         * @var \DOMDocument
         */
        protected $doc = null;

        /*********************************************************************/
        /* Framework                                                         */
        /*********************************************************************/

        public function tearDown() {
            unset($this->doc);
        }

        /*********************************************************************/
        /* Tests                                                             */
        /*********************************************************************/

        /**
         * @covers TheSeer\phpDox\DocBlock\DocBlock::asDom
         * @covers TheSeer\phpDox\DocBlock\DocBlock::appendElement
         */
        public function testAsDomWithSingleElement() {

            // create fDomDocument stub
            $doc = $this->getFDomDocumentFixture(array('createElementNS', 'createTextnode'));
            $doc
                ->expects($this->exactly(2))
                ->method('createElementNS')
                ->will($this->returnCallback(array($this, 'asDomCallback')));
            $doc
                ->expects($this->once())
                ->method('createTextnode')
                ->will($this->returnCallback(array($this, 'createTextnodeCallback')));

            // setup GenericDocument
            $element = new GenericElement('Tux');
            $element->setBody('Beastie');
            $element->setLabel('Linus');

            $docBlock = new DocBlock();
            $docBlock->appendElement($element);

            $domElement = $docBlock->asDom($doc);

            // attach generated DOMElement to a DOMDocument
            $fdoc = $this->getDomDocument();
            $fdoc->appendChild($domElement);

            $this->assertXmlStringEqualsXmlFile(
                __DIR__.'/../../data/documents/docBlockAsDom.xml',
                $fdoc->saveXML()
            );

        }


        /**
         * @covers TheSeer\phpDox\DocBlock\DocBlock::asDom
         * @covers TheSeer\phpDox\DocBlock\DocBlock::appendElement
         */
        public function testAsDomWithManyElements() {

            // create fDomDocument stub
            $doc = $this->getFDomDocumentFixture(array('createElementNS', 'createTextnode'));
            $doc
                ->expects($this->exactly(3))
                ->method('createElementNS')
                ->will($this->returnCallback(array($this, 'asDomCallback')));
            $doc
                ->expects($this->exactly(2))
                ->method('createTextnode')
                ->will($this->returnCallback(array($this, 'createTextnodeCallback')));

            // setup GenericDocument
            $element = new GenericElement('Beastie');
            $element->setBody('Gnu');
            $element->setLabel('Dolphin');

            $docBlock = new DocBlock();
            $docBlock->appendElement($element);
            $docBlock->appendElement($element);

            $domElement = $docBlock->asDom($doc);

            // attach generated DOMElement to a DOMDocument
            $fdoc = $this->getDomDocument();
            $fdoc->appendChild($domElement);

            $this->assertXmlStringEqualsXmlFile(
                __DIR__.'/../../data/documents/docBlockAsDomMultiple.xml',
                $fdoc->saveXML()
            );

        }

        /*********************************************************************/
        /* Dataprovider & Callbacks                                          */
        /*********************************************************************/

        /**
         * Provides a namespaced \DOMElement.
         *
         * @param string $namespaceURI
         * @param string $qualifiedName
         * @return \DOMElement
         */
        public function asDomCallback($namespaceURI , $qualifiedName) {
            $doc = $this->getDomDocument();
            return $doc->createElementNS($namespaceURI , $qualifiedName);
        }

        /**
         * Provides a namespaced \DOMElement.
         *
         * @param string $content
         * @return \DOMText
         */
        public function createTextnodeCallback($content) {
            $doc = $this->getDomDocument();
            return $doc->createTextnode($content);
        }
    }

}