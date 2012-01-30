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

namespace TheSeer\phpDox\Tests\Unit {

    use \lapistano\ProxyObject\ProxyObject;
    use \TheSeer\phpDox\Tests\phpDox_TestCase;
    use \TheSeer\phpDox\ClassBuilder;
    use \TheSeer\fDOM\fDOMElement;
    use \TheSeer\fDOM\fDOMDocument;

    class ClassBuilderTest extends phpDox_TestCase {


        /*********************************************************************/
        /* Fixtures                                                          */
        /*********************************************************************/

        /**
         * Provides as stub of the \TheSeer\fDOM\fDOMElement class.
         *
         * @param array $methods
         * @return \TheSeer\fDOM\fDOMElement
         */
        protected function getFDomElementFixture(array $methods = array()) {
            return $this->getMockBuilder('\\TheSeer\\fDOM\\fDOMElement')
                ->disableOriginalConstructor()
                ->setMethods($methods)
                ->getMock();
        }

        /**
         * Provides a stub of the \TheSeer\phpDox\DocBlock\Parser class.
         *
         * @param array $methods
         * @return \TheSeer\phpDox\DocBlock\Parser
         */
        protected function getParserFixture(array $methods = array()) {
            return $this->getMockBuilder('\\TheSeer\\phpDox\\DocBlock\\Parser')
                ->disableOriginalConstructor()
                ->setMethods($methods)
                ->getMock();
        }

        /**
         * Provides an instance of the \TheSeer\phpDox\classBuilderProxy class
         *
         * @return \TheSeer\phpDox\classBuilderProxy
         */
        protected function getClassBuilderProxyFixture() {
            $parser = $this->getParserFixture();
            $ctx = $this->getFDomElementFixture(array());
            return new classBuilderProxy($parser, $ctx, array());
        }

        /*********************************************************************/
        /* Tests                                                             */
        /*********************************************************************/

        /**
         * @dataProvider addReferenceNodeDataprovider
         * @covers \TheSeer\phpDox\ClassBuilder::addReferenceNode
         */
        public function testAddReferenceNode($nodeName, $inheritance, $classShortName, $classPath) {
            $node = $this->getFDomElementFixture(array('setAttribute'));
            $node
                ->expects($this->exactly(2))
                ->method('setAttribute')
                ->with(
                    $this->logicalOr (
                        $this->equalTo($inheritance),
                        $this->equalTo('full')
                    ),
                    $this->logicalOr (
                        $this->equalTo($classShortName),
                        $this->equalTo($classShortName)
                    )
                );

            $class = new \ReflectionClass($classPath);
            $context = $this->getFDomElementFixture(array('appendElementNS'));
            $context
                ->expects($this->once())
                ->method('appendElementNS')
                ->with(
                    $this->equalTo('http://xml.phpdox.de/src#'),
                    $this->equalTo($nodeName)
                )
                ->will($this->returnValue($node));

            $classBuilder = $this->getClassBuilderProxyFixture();
            $this->assertInstanceOf('\DomElement', $classBuilder->addReferenceNode($class, $context, $nodeName));
        }

        /**
         * @dataProvider addReferenceNodeWithNamspacedClassDataprovider
         * @covers \TheSeer\phpDox\ClassBuilder::addReferenceNode
         */
        public function testAddReferenceNodeWithNamspacedClass($nodeName, $inheritance, $classShortName, $classPath) {
            $node = $this->getFDomElementFixture(array('setAttribute'));
            $node
                ->expects($this->exactly(3))
                ->method('setAttribute')
                ->with(
                    $this->logicalOr(
                        $this->equalTo($inheritance),
                        $this->equalTo('namespace'),
                        $this->equalTo('full')
                    ),
                    $this->logicalOr(
                        $this->equalTo($classShortName),
                        $this->equalTo('TheSeer\\phpDox'),
                        $this->equalTo('TheSeer\\phpDox\\' . $classShortName)
                    )
                );

            $class = new \ReflectionClass($classPath);
            $context = $this->getFDomElementFixture(array('appendElementNS'));
            $context
                ->expects($this->once())
                ->method('appendElementNS')
                ->with(
                    $this->equalTo('http://xml.phpdox.de/src#'),
                    $this->equalTo($nodeName)
                )
                ->will($this->returnValue($node));

            $classBuilder = $this->getClassBuilderProxyFixture();
            $this->assertInstanceOf('\DomElement', $classBuilder->addReferenceNode($class, $context, $nodeName));
        }

        /**
         * @dataProvider addModifiersDataprovider
         * @covers \TheSeer\phpDox\ClassBuilder::addModifiers
         */
        public function testAddModifiers($isStatic, $visibility, $src) {
            $context = $this->getFDomElementFixture(array('setAttribute'));
            $context
                ->expects($this->exactly(2))
                ->method('setAttribute')
                ->with(
                    $this->logicalOr(
                        $this->equalTo('static'),
                        $this->equalTo('visibility')
                    ),
                    $this->logicalOr(
                        $this->equalTo($isStatic),
                        $this->equalTo($visibility)
                    )
                );
            $classBuilder = $this->getClassBuilderProxyFixture();
            $classBuilder->addModifiers($context, $src);
        }

        /**
         * @covers \TheSeer\phpDox\ClassBuilder::processDocBlock
         */
        public function testProcessDocBlock() {

            $factory = $this->getFactoryFixture(array());

            $docBlock = $this->getMockBuilder('\\TheSeer\\phpDox\\DocBlock\\DocBlock')
                ->getMock();

            $parser = $this->getMockBuilder('\\TheSeer\\phpDox\\DocBlock\\Parser')
                ->setConstructorArgs(array($factory))
                ->setMethods(array('parse'))
                ->getMock();
            $parser
                ->expects($this->once())
                ->method('parse')
                ->will($this->returnValue($docBlock));
            $ctx = $this->getFDomElementFixture(array());

            $comment = '
                /**
                 * shortdescription
                 *
                 * longdescription
                 * multiline
                 *
                 * @param  string   $tux  Description of tux.
                 * @return boolean        Signals the success or failure.
                 */
            ';

            $classBuilder = new classBuilderProxy($parser, $ctx, array());
            $classBuilder->processDocBlock($this->getFDomDocumentFixture(array()), $comment);
        }

        /**
         * @expectedException \TheSeer\phpDox\ClassBuilderException
         * @covers \TheSeer\phpDox\ClassBuilder::processDocBlock
         */
        public function testProcessDocBlockExpectingException() {
            $parser = $this->getParserFixture(array('parse'));
            $parser
                ->expects($this->once())
                ->method('parse')
                ->will($this->throwException(new \Exception()));
            $ctx = $this->getFDomElementFixture(array());

            $classBuilder = new classBuilderProxy($parser, $ctx, array());
            $classBuilder->processDocBlock($this->getFDomDocumentFixture(array()), '');
        }

        /**
         * @covers \TheSeer\phpDox\ClassBuilder::processConstants
         */
        public function testProcessConstants() {

            $constants = array(
                'TUX' => 'beastie'
            );

            $node = $this->getFDomElementFixture(array('setAttribute'));
            $node
                ->expects($this->exactly(2))
                ->method('setAttribute')
                ->with(
                    $this->logicalOr(
                        $this->equalTo('name'),
                        $this->equalTo('value')
                    ),
                    $this->logicalOr(
                        $this->equalTo('TUX'),
                        $this->equalTo('beastie')
                    )
                );

            $context = $this->getFDomElementFixture(array('appendElementNS'));
            $context
                ->expects($this->once())
                ->method('appendElementNS')
                ->will($this->returnValue($node));

            $classBuilder = $this->getClassBuilderProxyFixture();
            $classBuilder->processConstants($context, $constants);
        }

        /**
         * @covers \TheSeer\phpDox\ClassBuilder::processMembers
         */
        public function testProcessMembers() {

            $member = $this->getMockBuilder('\ReflectionProperty')
                ->disableOriginalConstructor()
                ->getMock();
            $member
                ->expects($this->once())
                ->method('isProtected')
                ->will($this->returnValue(true));

            $members = array(
                'TUX' => $member
            );

            $parser = $this->getParserFixture();
            $ctx = $this->getFDomElementFixture(array());
            $classBuilder = new classBuilderProxy($parser, $ctx, array(), true);
            $classBuilder->processMembers($ctx, $members);
        }

        /**
         * @covers \TheSeer\phpDox\ClassBuilder::processMethods
         */
        public function testProcessMethods() {

            $method = $this->getMockBuilder('\ReflectionMethod')
                ->disableOriginalConstructor()
                ->getMock();
            $method
                ->expects($this->once())
                ->method('isProtected')
                ->will($this->returnValue(true));

            $methods = array(
                'TUX' => $method
            );

            $parser = $this->getParserFixture();
            $ctx = $this->getFDomElementFixture(array());
            $classBuilder = new classBuilderProxy($parser, $ctx, array(), true);
            $classBuilder->processMethods($ctx, $methods);
        }

        /**
         * @covers \TheSeer\phpDox\ClassBuilder::processParameters
         */
        public function testProcessParameters() {

            $class = $this->getMockBuilder('\ReflectionClass')
                ->disableOriginalConstructor()
                ->getMock();
            $class
                ->expects($this->once())
                ->method('inNamespace')
                ->will($this->returnValue(true));
            $class
                ->expects($this->once())
                ->method('getShortName')
                ->will($this->returnValue('Beastie'));
            $class
                ->expects($this->once())
                ->method('getNamespaceName')
                ->will($this->returnValue('\\TheSeer\\phpDox\\'));

            $parameter = $this->getMockBuilder('\ReflectionParameter')
                ->disableOriginalConstructor()
                ->getMock();
            $parameter
                ->expects($this->once())
                ->method('getClass')
                ->will($this->returnValue($class));
            $parameter
                ->expects($this->once())
                ->method('getName')
                ->will($this->returnValue('Beastie'));
            $parameter
                ->expects($this->once())
                ->method('isOptional')
                ->will($this->returnValue(false));
            $parameter
                ->expects($this->once())
                ->method('isPassedByReference')
                ->will($this->returnValue(false));
            $parameter
                ->expects($this->once())
                ->method('isDefaultValueAvailable')
                ->will($this->returnValue(false));

            $parameters = array(
                'Tux' => $parameter,
            );

            $node = $this->getFDomElementFixture(array('setAttribute'));
            $node
                ->expects($this->exactly(6))
                ->method('setAttribute')
                ->with(
                    $this->logicalOr(
                        $this->equalTo('name'),
                        $this->equalTo('type'),
                        $this->equalTo('class'),
                        $this->equalTo('namespace'),
                        $this->equalTo('optional'),
                        $this->equalTo('byreference')
                    ),
                    $this->logicalOr(
                        $this->equalTo('$Beastie'),
                        $this->equalTo('object'),
                        $this->equalTo('Beastie'),
                        $this->equalTo('\\TheSeer\\phpDox\\'),
                        $this->equalTo('false')
                    )
                );

            $context = $this->getFDomElementFixture(array('appendElementNS'));
            $context
                ->expects($this->once())
                ->method('appendElementNS')
                ->will($this->returnValue($node));

            $classBuilder = new classBuilderProxy($this->getParserFixture(), $context, array());
            $classBuilder->processParameters($context, $parameters);
        }

        /**
         * @covers \TheSeer\phpDox\ClassBuilder::processValue
         */
        public function testProcessValues() {
            $doc = new fDOMDocument();
            $doc->loadXML('<tux/>');
            $ctx = $doc->getElementsByTagName('tux')->item(0);

            $classBuilder =
                new classBuilderProxy($this->getParserFixture(), $this->getFDomElementFixture(array()), array());
            $classBuilder->processValue($ctx, '\'__StaticReflectionConstantValue(');
        }

        /*********************************************************************/
        /* Dataprovider & callbacks                                          */
        /*********************************************************************/

        public static function addModifiersDataprovider() {

            $class = new \ReflectionClass('\\TheSeer\\phpDox\\Tests\\Fixtures\\Dummy');
            $members = $class->getProperties();

            return array(
                'protected' => array('false', 'protected', $members[0]),
                'static protected' => array('true', 'protected', $members[1]),
                'private' => array('false', 'private', $members[2]),
                'static private' => array('true', 'private', $members[3]),
                'public' => array('false', 'public', $members[4]),
                'static public' => array('true', 'public', $members[5]),
            );
        }

        public static function addReferenceNodeWithNamspacedClassDataprovider() {
            return array(
                'inheritance in namespace' =>
                    array('extends', 'class', 'ClassBuilder', '\\TheSeer\\phpDox\\ClassBuilder'),
                'implementation in namespace' =>
                    array('implements', 'interface', 'ClassBuilder', '\\TheSeer\\phpDox\\ClassBuilder'),
            );
        }

        public static function addReferenceNodeDataprovider() {
            return array(
                'inheritance' => array('extends', 'class', 'stdClass', '\\stdClass'),
                'implementation' => array('implements', 'interface', 'stdClass', '\\stdClass'),
            );
        }

    }

    class classBuilderProxy extends \TheSeer\phpDox\ClassBuilder {

        public function addReferenceNode(\ReflectionClass $class, fDOMElement $context, $nodeName) {
            return parent::addReferenceNode($class, $context, $nodeName);
        }
        public function addModifiers(fDOMElement $ctx, $src) {
            return parent::addModifiers($ctx, $src);
        }
        public function processDocBlock(fDOMDocument $doc, $comment) {
            return parent::processDocBlock($doc, $comment);
        }
        public function processConstants(fDOMElement $ctx, Array $constants) {
            return parent::processConstants($ctx, $constants);
        }
        public function processMembers(fDOMElement $ctx, Array $members) {
            return parent::processMembers($ctx, $members);
        }
        public function processMethods(fDOMElement $ctx, Array $methods) {
            return parent::processMethods($ctx, $methods);
        }
        public function processParameters(fDOMElement $ctx, Array $parameters, fDOMElement $docBlock = null) {
            return parent::processParameters($ctx, $parameters, $docBlock);
        }
        public function processValue(fDOMElement $ctx, $src) {
            return parent::processValue($ctx, $src);
        }
    }
}