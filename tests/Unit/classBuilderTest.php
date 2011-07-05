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

    use lapistano\ProxyObject\ProxyObject;
    use TheSeer\phpDox\Tests\phpDox_TestCase;
    use TheSeer\phpDox\ClassBuilder;

    class ClassBuilderTest extends phpDox_TestCase {


        /*********************************************************************/
        /* Fixtures                                                          */
        /*********************************************************************/

        protected function getFDomElementFixture (array $methods) {
            return $this->getMockBuilder('\\TheSeer\\fDOM\\fDOMElement')
                ->disableOriginalConstructor()
                ->setMethods($methods)
                ->getMock();
        }

        /*********************************************************************/
        /* Tests                                                             */
        /*********************************************************************/

        /**
         * @covers \TheSeer\phpDox\ClassBuilder::addReferenceNode
         */
        public function dtestAddReferenceNode() {
            $nodeName = 'implemts'; // extends

            $dom = $this->getDomDocument();
            $newNode = new \DOMElement($nodeName);
            $dom->appendChild($newNode);
            $node = $dom->getElementsByTagname($nodeName)->item(0);

            $class = new \ReflectionClass('\\stdClass');
            $context = $this->getFDomElementFixture(array('appendElementNS'));
            $context
                ->expects($this->once())
                ->method('appendElementNS')
                ->with(
                    $this->logicalAnd(
                        $this->equalTo('http://xml.phpdox.de/src#'),
                        $this->equalTo('class')
                    )
                )
                ->will($this->returnValue($node));

            $proxy = new ProxyObject();
            $classBuilder = $proxy->getProxyBuilder('\\TheSeer\\phpDox\\ClassBuilder')
                ->disableOriginalConstructor()
                ->setMethods(array('addReferenceNode'))
                ->getProxy();

            $actualNode = $classBuilder->addReferenceNode($class, $context, $nodeName);

            var_dump($actualNode);

            $this->markTestIncomplete('Due to bug in proxy-object.');
        }
    }
}