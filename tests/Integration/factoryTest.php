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
 * @author     Bastian Feder <phpdox@bastian-feder.de>
 * @copyright  Arne Blankerts <arne@blankerts.de>, All rights reserved.
 * @license    BSD License
 */

namespace TheSeer\phpDox\Tests\Integration {

    use TheSeer\phpDox\Factory;

    class FactoryTest extends \PHPUnit_Framework_TestCase {

        /*********************************************************************/
        /* Fixtures                                                          */
        /*********************************************************************/

        /**
         * Provides a stub of the TheSeer\\phpDox\Container class.
         *
         * @param array $methods
         * @return TheSeer\\phpDox\Container
         */
        protected function getContainerFixture(array $methods) {
            return $this->getMockBuilder('TheSeer\\phpDox\Container')
                ->disableOriginalConstructor()
                ->setMethods($methods)
                ->getMock();
        }


        /*********************************************************************/
        /* Tests                                                             */
        /*********************************************************************/

        /**
         * @covers TheSeer\phpDox\Factory::getAnalyser
         */
        public function testGetAnalyser() {
            $factory = new Factory();
            $this->assertInstanceOf('TheSeer\\phpDox\\Analyser', $factory->getInstanceFor('Analyser', array(true)));
        }

        /**
         * @covers TheSeer\phpDox\Factory::getApi
         */
        public function testGetApi() {
            $factory = new Factory();
            $this->assertInstanceOf('TheSeer\\phpDox\\Api', $factory->getInstanceFor('Api'));
        }

        /**
         * @dataProvider getLoggerDataprovider
         * @covers TheSeer\phpDox\Factory::getLogger
         */
        public function testGetLogger($expected, $argument) {
            $factory = new Factory();
            $this->assertInstanceOf($expected, $factory->getLogger($argument));
        }

        /**
         * @covers TheSeer\phpDox\Factory::getApplication
         */
        public function testGetApplication() {
            $factory = new Factory();
            $factory->setXmlDir('/path');

            $this->assertInstanceOf(
                'TheSeer\\phpDox\\Application',
                $factory->getInstanceFor('Application')
            );
        }

        /**
         * @covers TheSeer\phpDox\Factory::getContainer
         */
        public function testGetContainer() {
            $factory = new Factory();
            $container = $factory->getInstanceFor('Container');

            // lazy initialization included
            $this->assertInstanceOf(
                'TheSeer\\phpDox\\Container',
                $container
            );

            $this->assertSame($container, $factory->getInstanceFor('Container'));
        }

        /**
         * @covers TheSeer\phpDox\Factory::getScanner
         */
        public function testGetScanner() {

        }

        /**
         * @covers TheSeer\phpDox\Factory::getCollector
         */
        public function testGetCollector() {

            $container = $this->getContainerFixture(array('getDocument'));
            $container
                ->expects($this->exactly(3))
                ->method('getDocument')
                ->will($this->returnValue(new \stdClass));

            $factory = new FactoryProxy();
            $factory->instances['container'] = $container;
            $factory->setXmlDir('/path');

            $this->assertInstanceOf(
                'TheSeer\\phpDox\\Collector',
                $factory->getInstanceFor('Collector')
            );
        }

        /**
         * @covers TheSeer\phpDox\Factory::getGenerator
         */
        public function testGetGenerator() {
            $container = $this->getContainerFixture(array('getDocument'));
            $container
                ->expects($this->exactly(3))
                ->method('getDocument')
                ->will($this->returnValue(new \stdClass));

            $factory = new FactoryProxy();
            $factory->instances['container'] = $container;
            $factory->setXmlDir('/path');

            $this->assertInstanceOf(
                'TheSeer\\phpDox\\Generator',
                $factory->getInstanceFor('Generator', '/tplDir', '/docDir')
            );
        }

        /**
         * @covers TheSeer\phpDox\Factory::getClassBuilder
         */
        public function testGetClassBuilder() {
            $doc = $this->getMockBuilder('TheSeer\\fDOM\\fDOMElement')
                ->disableOriginalConstructor()
                ->getMock();

            $factory = new Factory();

            $this->assertInstanceOf(
                'TheSeer\\phpDox\\ClassBuilder',
                $factory->getInstanceFor('ClassBuilder', $doc, true, 'UTF-8')
            );
        }

        /**
         * @covers TheSeer\phpDox\Factory::getDocblockFactory
         */
        public function testgetDoclockFactory() {
            $factory = new Factory();
            $docBlock = $factory->getInstanceFor('DocblockFactory');

            // lazy initialization included
            $this->assertInstanceOf(
                'TheSeer\\phpDox\\DocBlock\\Factory',
                $docBlock
            );

            $this->assertSame($docBlock, $factory->getInstanceFor('DocblockFactory'));
        }

        /**
         * @covers TheSeer\phpDox\Factory::getDocblockParser
         */
        public function testgetDoclockParser() {
            $factory = new Factory();
            $docBlock = $factory->getInstanceFor('DocblockParser');

            // lazy initialization included
            $this->assertInstanceOf(
                'TheSeer\\phpDox\\DocBlock\\Parser',
                $docBlock
            );

            $this->assertSame($docBlock, $factory->getInstanceFor('DocblockParser'));
        }


        /*********************************************************************/
        /* Dataprovider & Callbacks                                          */
        /*********************************************************************/

        public static function getLoggerDataprovider() {
            return array(
                'shell logger' => array('TheSeer\\phpDox\\ShellProgressLogger', 'shell'),
                'silent logger' => array('TheSeer\\phpDox\\ProgressLogger', 'silent'),
            );
        }
    }

    class FactoryProxy extends Factory {
        public $instances = array();
    }
}