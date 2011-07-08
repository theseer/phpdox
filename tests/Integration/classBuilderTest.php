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

namespace TheSeer\phpDox\Tests\Integration {

    use \TheSeer\fDOM\fDOMDocument;
    use TheSeer\fDOM\fDOMElement;

    use TheSeer\phpDox\Tests\phpDox_TestCase;
    use TheSeer\phpDox\ClassBuilder;

    class ClassBuilderTest extends phpDox_TestCase {

        public function setUp() {

            if (!class_exists('\\TheSeer\\fDOM\\fDOMDocument')) {
                $this->markTestSkipped('Mandatory dependency (TheSeer\FDom\fDOMDocument) not available.');
            }
        }

        /*********************************************************************/
        /* Fixtures                                                          */
        /*********************************************************************/

        /**
         * Provides an instance ot the \TheSeer\fDOM\fDOMElement class.
         *
         * @return \TheSeer\fDOM\fDOMElement
         */
        protected function getFDomElementFixture() {
            $dom = new fDOMDocument();
            $dom->formatOutput = true;
            $element = $dom->createElement('tux');
            $dom->appendChild($element);
            return $dom->getElementsByTagName('tux')->item(0);
        }

        /**
         * Provides an instance of \TheSeer\phpDox\DocBlock\Factory
         *
         * @return \TheSeer\phpDox\DocBlock\Factory
         */
        protected function getFactoryInstanceFixture() {
            $map = array(
            'DocBlock' => '\\TheSeer\\phpDox\\DocBlock\\DocBlock'
        );
            return new \TheSeer\phpDox\DocBlock\Factory($map);
        }

        /**
         * Provides an instance of \TheSeer\phpDox\DocBlock\Parser
         *
         * @return \TheSeer\phpDox\DocBlock\Parser
         */
        protected function getParserFixture() {
            return new \TheSeer\phpDox\DocBlock\Parser($this->getFactoryInstanceFixture());
        }

        /*********************************************************************/
        /* Tests                                                             */
        /*********************************************************************/

        /**
         * @dataProvider processDataprovider
         */
        public function testProcess($expected, $classname) {

            $ctx = $this->getFDomElementFixture();
            $aliasMap = array();
            $class = new \ReflectionClass($classname);
            $parser = $this->getParserFixture();

            $classBuilder = new ClassBuilder($parser, $ctx, $aliasMap, false, 'UTF-8');
            $node = $classBuilder->process($class);
            $this->assertXmlStringEqualsXmlFile($expected, $node->ownerDocument->saveXML());
        }

        /**
         * @covers \TheSeer\phpDox\ClassBuilder::processMethods
         * @group issue#0
         */
        public function testProcessInterfaceTypeHintInConstructerArgs() {

            $expected  = __DIR__.'/../data/issues/issue#0/parsedDummyClass.xml';
            $classname = '\\TheSeer\\phpDox\\Tests\\Issues\\Fixtures\\Dummy';

            $ctx = $this->getFDomElementFixture();
            $aliasMap = array();
            $class = new \ReflectionClass($classname);
            $parser = $this->getParserFixture();

            $classBuilder = new ClassBuilder($parser, $ctx, $aliasMap, false, 'UTF-8');
            $node = $classBuilder->process($class);
            $this->assertXmlStringEqualsXmlFile($expected, $node->ownerDocument->saveXML());
        }

        /**
         * @covers \TheSeer\phpDox\ClassBuilder::processMembers
         * @group issue#1
         */
        public function testProcessUninstantiableClass() {

            $expected  = __DIR__.'/../data/issues/issue#0/parsedDummyClass.xml';
            $classname = '\\TheSeer\\phpDox\\Tests\\Issues\\Fixtures\\DummyAbstract';

            $ctx = $this->getFDomElementFixture();
            $aliasMap = array();
            $class = new \ReflectionClass($classname);
            $parser = $this->getParserFixture();

            $classBuilder = new ClassBuilder($parser, $ctx, $aliasMap, false, 'UTF-8');
            $node = $classBuilder->process($class);
            $this->assertXmlStringEqualsXmlFile($expected, $node->ownerDocument->saveXML());
        }

        /*********************************************************************/
        /* Dataprovider & Callbacks                                          */
        /*********************************************************************/

        public static function processDataprovider() {
            return array(
                'simple class' => array(
                    __DIR__.'/../data/documents/parsedDummyClass.xml',
                    '\\TheSeer\\phpDox\\Tests\\Fixtures\\Dummy'
                ),
                'class extending \stdClass' => array(
                    __DIR__.'/../data/documents/parsedDummyExtendingParentClass.xml',
                    '\\TheSeer\\phpDox\\Tests\\Fixtures\\DummyExtendingParent'
                ),
                'class implementing \Countable' => array(
                    __DIR__.'/../data/documents/parsedDummyImplementingInterfaceClass.xml',
                    '\\TheSeer\\phpDox\\Tests\\Fixtures\\DummyImplementingInterface'
                ),
            );
        }

    }
}