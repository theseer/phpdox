<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Tests\Unit\DocBlock;

use TheSeer\fDOM\fDOMDocument;
use TheSeer\phpDox\DocBlock\Factory;
use TheSeer\phpDox\DocBlock\GenericParser;
use TheSeer\phpDox\DocBlock\InlineProcessor;
use TheSeer\phpDox\FactoryInterface;

/**
 * @covers \TheSeer\phpDox\DocBlock\Factory
 */
class FactoryTest extends \PHPUnit\Framework\TestCase {
    private $factory;

    /**/
    /* Dataprovider                                                      */
    /**/

    public static function addParserClassDataprovider() {
        return [
            'wrong annotation type' => [[], 'Gnu'],
            'wrong classname type'  => ['Tux', []],
        ];
    }

    protected function setUp(): void {
        $this->factory = new Factory();
    }

    /**
     * @covers \TheSeer\phpDox\DocBlock\Factory::addParserFactory
     */
    public function testAddParserFactory(): void {
        $mock = $this->createMock(FactoryInterface::class);
        $this->factory->addParserFactory('Tux', $mock);
        $this->assertAttributeContains($mock, 'parserMap', $this->factory);
    }

    /**
     * @expectedException \TheSeer\phpDox\DocBlock\FactoryException
     * @covers \TheSeer\phpDox\DocBlock\Factory::addParserFactory
     */
    public function testAddParserFactoryExpectingFactoryException(): void {
        $mock = $this->createMock(FactoryInterface::class);
        $this->factory->addParserFactory([], $mock);
    }

    /**
     * @covers \TheSeer\phpDox\DocBlock\Factory::addParserClass
     */
    public function testAddParserClass(): void {
        $this->factory->addParserClass('Tux', 'Gnu');
        $this->assertAttributeContains('Gnu', 'parserMap', $this->factory);
    }

    /**
     * @dataProvider addParserClassDataprovider
     * @expectedException \TheSeer\phpDox\DocBlock\FactoryException
     * @covers       \TheSeer\phpDox\DocBlock\Factory::addParserClass
     */
    public function testAddParserClassExpectingFactoryException($annotation, $classname): void {
        $this->factory->addParserClass($annotation, $classname);
    }

    /**
     * @covers \TheSeer\phpDox\DocBlock\Factory::getDocBlock
     *
     * @uses   \TheSeer\phpDox\DocBlock\DocBlock
     */
    public function testGetInstanceForDocBlock(): void {
        $factory = new Factory();
        $this->assertInstanceOf(
            'TheSeer\\phpDox\\DocBlock\\DocBlock',
            $factory->getDocBlock()
        );
    }

    /**
     * @covers \TheSeer\phpDox\DocBlock\Factory::getInlineProcessor
     *
     * @uses   \TheSeer\phpDox\DocBlock\InlineProcessor
     */
    public function testGetInstanceForInlineProcessor(): void {
        $factory = new Factory();
        $this->assertInstanceOf(
            InlineProcessor::class,
            $factory->getInlineProcessor(new fDOMDocument())
        );
    }

    /**
     * @covers \TheSeer\phpDox\DocBlock\Factory::getParserInstanceFor
     *
     * @uses   \TheSeer\phpDox\DocBlock\GenericParser
     */
    public function testGetParserInstanceForUnknownNameReturnsGenericParser(): void {
        $factory = new Factory();
        $this->assertInstanceOf(
            GenericParser::class,
            $factory->getParserInstanceFor('Unknown Name Parser')
        );
    }
}
