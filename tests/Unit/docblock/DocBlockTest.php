<?php declare(strict_types = 1);
namespace TheSeer\phpDox\DocBlock;

use PHPUnit\Framework\TestCase;
use TheSeer\fDOM\fDOMDocument;

/**
 * Class DocBlockTest
 *
 * @covers \TheSeer\phpDox\DocBlock\DocBlock
 *
 * @uses   \TheSeer\phpDox\DocBlock\GenericElement
 */
class DocBlockTest extends TestCase {
    /**
     * @var DocBlock
     */
    private $docBlock;

    /**
     * @var GenericElement
     */
    private $element;

    protected function setUp(): void {
        $this->docBlock = new DocBlock();

        $this->element = $this->getMockBuilder('TheSeer\\phpDox\\DocBlock\\GenericElement')
            ->disableOriginalConstructor()
            ->getMock();

        $this->element->expects($this->any())
            ->method('getAnnotationName')
            ->willReturn('stub');
    }

    public function testHasElementByNameReturnsFalseIfNotPresent(): void {
        $this->assertFalse($this->docBlock->hasElementByName('not-set'));
    }

    /**
     * @covers \TheSeer\phpDox\DocBlock\DocBlock::appendElement
     * @covers \TheSeer\phpDox\DocBlock\DocBlock::hasElementByName
     */
    public function testElementCanBeAdded(): void {
        $this->docBlock->appendElement($this->element);
        $this->assertTrue($this->docBlock->hasElementByName('stub'));
    }

    /**
     * @covers \TheSeer\phpDox\DocBlock\DocBlock::appendElement
     */
    public function testSameTypeElementCanBeAddedMultipleTimes(): void {
        $this->docBlock->appendElement($this->element);
        $this->docBlock->appendElement($this->element);
        $this->assertTrue($this->docBlock->hasElementByName('stub'));
        $this->assertCount(2, $this->docBlock->getElementByName('stub'));
    }

    /**
     * @expectedException \TheSeer\phpDox\DocBlock\DocBlockException
     * @covers \TheSeer\phpDox\DocBlock\DocBlock::getElementByName
     */
    public function testTryingToGetANonExistingElementThrowsException(): void {
        $this->docBlock->getElementByName('non-set');
    }

    /**
     * @covers \TheSeer\phpDox\DocBlock\DocBlock::getElementByName
     */
    public function testElementCanBeRetreived(): void {
        $this->docBlock->appendElement($this->element);
        $this->assertEquals($this->element, $this->docBlock->getElementByName('stub'));
    }

    public function testDocBlockCanBeSerializedToDom(): void {
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

    public function testDocBlockWithMultipleOccurencesOfAnnotationCanBeSerializedToDom(): void {
        $dom = new fDOMDocument();
        $dom->registerNamespace('test', 'http://xml.phpdox.net/src');

        $element2 = clone $this->element;
        $this->element->expects($this->once())
            ->method('asDom')
            ->willReturn($dom->createElementNS('http://xml.phpdox.net/src', 'stub'));

        $element2->expects($this->once())
            ->method('asDom')
            ->willReturn($dom->createElementNS('http://xml.phpdox.net/src', 'stub'));

        $this->docBlock->appendElement($this->element);
        $this->docBlock->appendElement($element2);

        $node = $this->docBlock->asDom($dom);

        $this->assertEquals(
            '<docblock xmlns="http://xml.phpdox.net/src"><stub/><stub/></docblock>',
            $dom->saveXML($node)
        );
    }
}
