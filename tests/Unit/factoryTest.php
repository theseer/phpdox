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

    use TheSeer\phpDox\Factory;

    /**
     * Class FactoryTest
     *
     * @covers TheSeer\phpDox\Factory
     */
    class FactoryTest extends \PHPUnit_Framework_TestCase {

        /**
         * @covers TheSeer\phpDox\Factory::__construct
         * @covers TheSeer\phpDox\Factory::setXMLDir
         */
        /*
        public function testSetXMLDir() {
            $factory = new Factory(array('Tux' => 'Gnu'));
            $factory->setXMLDir('/os/mascott/Tux');

            $this->assertAttributeEquals('/os/mascott/Tux', 'xmlDir', $factory);
            $this->assertAttributeEquals(array('Tux' => 'Gnu'), 'map', $factory);
        }
        */

        /**
         * @covers TheSeer\phpDox\Factory::addFactory
         */
        public function testAddFactory() {
            $factory = new Factory();
            $factory->addFactory('Gnu', $factory);
            $actual = $this->readAttribute($factory, 'map');

            $this->assertInstanceOf('TheSeer\phpDox\Factory', $actual['Gnu']);
        }

        /**
         * @covers TheSeer\phpDox\Factory::addClass
         */
        public function testAddClass() {
            $factory = new Factory();
            $factory->addClass('Gnu', 'myClass');
            $actual = $this->readAttribute($factory, 'map');

            $this->assertEquals('myClass', $actual['Gnu']);
        }

        /**
         * @covers TheSeer\phpDox\Factory::getInstanceFor
         */
        public function testGetInstanceForClass() {
            $factory = new Factory();
            $factory->addClass('Gnu', 'TheSeer\\phpDox\\Factory');

            $this->assertInstanceOf('TheSeer\\phpDox\\Factory', $factory->getInstanceFor('Gnu'));
        }

        /**
         * @covers TheSeer\phpDox\Factory::getInstanceFor
         */
        public function testGetInstanceForClassWithParameterArray() {
            $factory = new Factory();
            $factory->addClass('Gnu', 'TheSeer\\phpDox\\Factory');

            $this->assertInstanceOf('TheSeer\\phpDox\\Factory', $factory->getInstanceFor('Gnu', array('Tux' =>200)));
        }

        /**
         * @covers TheSeer\phpDox\Factory::getInstanceFor
         */
        public function testGetInstanceForClassWithParameterString() {
            $factory = new Factory();
            $factory->addClass('Gnu', 'Exception');

            $this->assertInstanceOf('Exception', $factory->getInstanceFor('Gnu', 'Tux', 200));
        }

        /**
         * @covers TheSeer\phpDox\Factory::getInstanceFor
         * @uses TheSeer\phpDox\Factory
         */
        public function testGetInstanceForFactory() {
            $factory = new Factory();
            $factory->addClass('TheSeer\\phpDox\\Factory', new Factory());

            $this->assertInstanceOf('TheSeer\\phpDox\\Factory', $factory->getInstanceFor('TheSeer\\phpDox\\Factory'));
        }

        /**
         * @covers TheSeer\phpDox\Factory::getInstanceFor
         * @uses TheSeer\phpDox\ProgressLogger
         */
        public function testGetInstanceForMethod() {
            $factory = new Factory();

            $this->assertInstanceOf('TheSeer\phpDox\ProgressLogger', $factory->getInstanceFor('Logger', 'silent'));
        }

        /**
         * @dataProvider getGenericInstanceDataprovider
         * @covers TheSeer\phpDox\Factory::getGenericInstance
         */
        public function testgetGenericInstance($expected, $classname, $arguments) {
            $factory = new FactoryProxy();
            $this->assertInstanceOf($expected, $factory->getGenericInstance($classname, $arguments));
        }

        /**
         * @expectedException \TheSeer\phpDox\FactoryException
         * @dataProvider getGenericInstanceExpectionFactoryExceptionDataprovider
         * @covers TheSeer\phpDox\Factory::getGenericInstance
         */
        public function testgetGenericInstanceExpectingFactoryException($classname, $argument) {
            $factory = new FactoryProxy();
            $factory->getGenericInstance($classname, $argument);

        }

        /**
         * @dataProvider getScannerDataproviderNoExclude
         * @covers TheSeer\phpDox\Factory::getScanner
         */
        public function testGetScannerNoExclude($methodName, $include) {

            $directoryScanner = $this->getMockBuilder('\\stdClass')
                ->setMethods(array($methodName))
                ->getMock();

            $directoryScanner
                ->expects($this->once())
                ->method($methodName)
                ->with($this->equalTo($include));

            $factory = $this->getMockBuilder('\\TheSeer\\phpDox\\Tests\\Unit\\FactoryProxy')
                ->setMethods(array('getInstanceFor'))
                ->getMock();
            $factory
                ->expects($this->once())
                ->method('getInstanceFor')
                ->with($this->equalTo('DirectoryScanner'))
                ->will($this->returnValue($directoryScanner));

                $this->assertInstanceOf('\\stdClass', $factory->getScanner($include));
        }

        /**
         * @dataProvider getScannerDataproviderWithExclude
         * @covers TheSeer\phpDox\Factory::getScanner
         */
        public function testGetScannerWithExclude($methodName, $include, $exclude) {

            $directoryScanner = $this->getMockBuilder('\\stdClass')
                ->setMethods($methodName)
                ->getMock();

            $directoryScanner
                ->expects($this->once())
                ->method($methodName[0])
                ->with($this->equalTo($include));

            $directoryScanner
                ->expects($this->once())
                ->method($methodName[1])
                ->with($this->equalTo($exclude));

            $factory = $this->getMockBuilder('\\TheSeer\\phpDox\\Tests\\Unit\\FactoryProxy')
                ->setMethods(array('getInstanceFor'))
                ->getMock();
            $factory
                ->expects($this->once())
                ->method('getInstanceFor')
                ->with($this->equalTo('DirectoryScanner'))
                ->will($this->returnValue($directoryScanner));

                $this->assertInstanceOf('\\stdClass', $factory->getScanner($include, $exclude));
        }


        /*********************************************************************/
        /* Dataprovider & Callbacks                                          */
        /*********************************************************************/

        public static function getScannerDataproviderNoExclude() {
            return array(
                'include as string' => array('addInclude', 'myInclude'),
                'include as array' => array('setIncludes', array('myInclude')),
            );
        }

        public static function getScannerDataproviderWithExclude() {
            return array(
                'exclude as string' => array(array('addInclude', 'addExclude'), 'myInclude', 'myExclude'),
                'exclude as array' => array(array('setIncludes', 'setExcludes'), array('myInclude'), array('myExclude')),
            );
        }

        public static function getGenericInstanceDataprovider() {
            return array(
                'class with no arguments' => array('TheSeer\\phpDox\\Factory', 'TheSeer\\phpDox\\Factory', array()),
                'class with array as argument' => array('TheSeer\\phpDox\\Factory', 'TheSeer\\phpDox\\Factory', array(array())),
            );
        }

        public static function getGenericInstanceExpectionFactoryExceptionDataprovider() {
            return array(
                'not instanciable' => array('Iterator', array()),
                'no constructor' => array('stdClass', array(array())),
            );
        }
    }


    class FactoryProxy extends Factory {

        public function getScanner($include, $exclude = null) {
            return parent::getScanner($include, $exclude);
        }

        public function getGenericInstance($class, array $params) {
            return parent::getGenericInstance($class, $params);
        }
    }
}