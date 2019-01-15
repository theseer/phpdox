<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Tests\Unit\DocBlock;

use TheSeer\fDOM\fDOMDocument;
use TheSeer\phpDox\DocBlock\InvalidElement;

/**
 * Class InvalidElementTest
 *
 * @covers \TheSeer\phpDox\DocBlock\InvalidElement
 *
 * @uses   \TheSeer\phpDox\DocBlock\InvalidElement
 * @uses   \TheSeer\phpDox\DocBlock\GenericElement
 */
class InvalidElementTest extends \PHPUnit\Framework\TestCase {
    /**
     * @covers \TheSeer\phpDox\DocBlock\InvalidElement::asDom
     */
    public function testElementCanBeSerializedToDom(): void {
        $dom     = new fDOMDocument();
        $element = new InvalidElement(
            $this->createMock(\TheSeer\phpDox\DocBlock\Factory::class),
            'test'
        );

        $this->assertEquals(
            '<invalid xmlns="http://xml.phpdox.net/src" annotation="test"/>',
            $dom->saveXML($element->asDom($dom))
        );
    }
}
