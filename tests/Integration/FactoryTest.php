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
namespace TheSeer\phpDox\Tests\Integration;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TheSeer\phpDox\Application;
use TheSeer\phpDox\Collector\Collector;
use TheSeer\phpDox\CollectorConfig;
use TheSeer\phpDox\DocBlock\Parser;
use TheSeer\phpDox\ErrorHandler;
use TheSeer\phpDox\Factory;
use TheSeer\phpDox\FileInfo;
use TheSeer\phpDox\Generator\Generator;
use TheSeer\phpDox\Version;

/**
 * Class FactoryTest
 *
 * @covers \TheSeer\phpDox\Factory
 * @uses   \TheSeer\phpDox\version
 */
class FactoryTest extends TestCase {

    /**
     * @var Factory
     */
    private $factory;

    protected function setUp() {
        $this->factory = new Factory(new FileInfo(__DIR__), new Version('0.0'));
    }

    /**
     * @covers \TheSeer\phpDox\Factory::getApplication
     * @uses   \TheSeer\phpDox\Application
     * @uses   \TheSeer\phpDox\ShellProgressLogger
     */
    public function testGetApplication() {
        $this->assertInstanceOf(
            Application::class,
            $this->factory->getApplication()
        );
    }

    /**
     * @covers \TheSeer\phpDox\Factory::getCollector
     * @uses   \TheSeer\phpDox\ShellProgressLogger
     * @uses   \TheSeer\phpDox\FileInfo
     * @uses   \TheSeer\phpDox\Collector\Collector
     * @uses   \TheSeer\phpDox\Collector\IndexCollection
     * @uses   \TheSeer\phpDox\Collector\SourceCollection
     * @uses   \TheSeer\phpDox\Collector\Project
     * @uses   \TheSeer\phpDox\Collector\Backend\Factory
     * @uses   \TheSeer\phpDox\Collector\Backend\PHPParser
     * @uses   \TheSeer\phpDox\DocBlock\Parser
     * @uses   \TheSeer\phpDox\ErrorHandler
     */
    public function testGetCollector() {
        /** @var CollectorConfig|MockObject $config */
        $config = $this->getMockBuilder(CollectorConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $config->expects($this->once())
            ->method('getSourceDirectory')
            ->willReturn(new FileInfo(''));

        $config->expects($this->once())
            ->method('getWorkDirectory')
            ->willReturn(new FileInfo(''));

        $config->expects($this->once())
            ->method('getBackend')
            ->willReturn('parser');

        $this->assertInstanceOf(
            Collector::class,
            $this->factory->getCollector($config)
        );
    }

    /**
     * @covers \TheSeer\phpDox\Factory::getGenerator
     * @uses   \TheSeer\phpDox\Generator\Generator
     * @uses   \TheSeer\phpDox\ShellProgressLogger
     */
    public function testGetGenerator() {
        $this->assertInstanceOf(
            Generator::class,
            $this->factory->getGenerator()
        );
    }

    /**
     * @covers \TheSeer\phpDox\Factory::getDocblockFactory
     * @uses   \TheSeer\phpDox\DocBlock\Factory
     */
    public function testgetDoclockFactory() {
        $docBlock = $this->factory->getDocblockFactory();

        // lazy initialization included
        $this->assertInstanceOf(
            \TheSeer\phpDox\DocBlock\Factory::class,
            $docBlock
        );

        $this->assertSame($docBlock, $this->factory->getDocblockFactory());
    }

    /**
     * @covers \TheSeer\phpDox\Factory::getDocblockParser
     * @uses   \TheSeer\phpDox\DocBlock\Parser
     */
    public function testgetDoclockParser() {
        $docBlock = $this->factory->getDocblockParser();

        // lazy initialization included
        $this->assertInstanceOf(
            Parser::class,
            $docBlock
        );

        $this->assertSame($docBlock, $this->factory->getDocblockParser());
    }

}
