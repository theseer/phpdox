<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Tests\Integration;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TheSeer\phpDox\Application;
use TheSeer\phpDox\Collector\Collector;
use TheSeer\phpDox\CollectorConfig;
use TheSeer\phpDox\DocBlock\Parser;
use TheSeer\phpDox\Factory;
use TheSeer\phpDox\FileInfo;
use TheSeer\phpDox\Generator\Generator;
use TheSeer\phpDox\Version;

/**
 * Class FactoryTest
 *
 * @covers \TheSeer\phpDox\Factory
 *
 * @uses   \TheSeer\phpDox\version
 */
class FactoryTest extends TestCase {
    /**
     * @var Factory
     */
    private $factory;

    protected function setUp(): void {
        $this->factory = new Factory(new FileInfo(__DIR__), new Version('0.0'));
    }

    /**
     * @covers \TheSeer\phpDox\Factory::getApplication
     *
     * @uses   \TheSeer\phpDox\Application
     * @uses   \TheSeer\phpDox\ShellProgressLogger
     */
    public function testGetApplication(): void {
        $this->assertInstanceOf(
            Application::class,
            $this->factory->getApplication()
        );
    }

    /**
     * @covers \TheSeer\phpDox\Factory::getCollector
     *
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
    public function testGetCollector(): void {
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
     *
     * @uses   \TheSeer\phpDox\Generator\Generator
     * @uses   \TheSeer\phpDox\ShellProgressLogger
     */
    public function testGetGenerator(): void {
        $this->assertInstanceOf(
            Generator::class,
            $this->factory->getGenerator()
        );
    }

    /**
     * @covers \TheSeer\phpDox\Factory::getDocblockFactory
     *
     * @uses   \TheSeer\phpDox\DocBlock\Factory
     */
    public function testgetDoclockFactory(): void {
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
     *
     * @uses   \TheSeer\phpDox\DocBlock\Parser
     */
    public function testgetDoclockParser(): void {
        $docBlock = $this->factory->getDocblockParser();

        // lazy initialization included
        $this->assertInstanceOf(
            Parser::class,
            $docBlock
        );

        $this->assertSame($docBlock, $this->factory->getDocblockParser());
    }
}
