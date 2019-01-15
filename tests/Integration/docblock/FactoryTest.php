<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Tests\Integration\DocBlock;

use TheSeer\phpDox\DocBlock\Factory;
use TheSeer\phpDox\FactoryInterface;

/**
 * Class FactoryTest
 *
 * @covers \TheSeer\phpDox\DocBlock\Factory
 */
class FactoryTest extends \PHPUnit\Framework\TestCase {

    /**/
    /* Dataprovider                                                      */
    /**/

    public static function getInstanceMapDataprovider() {
        $elementMap = [
            'invalid' => 'TheSeer\\phpDox\\DocBlock\\InvalidElement',
            'generic' => 'TheSeer\\phpDox\\DocBlock\\GenericElement'
        ];

        $parserMap = [
            'invalid' => 'TheSeer\\phpDox\\DocBlock\\InvalidParser',
            'generic' => 'TheSeer\\phpDox\\DocBlock\\GenericParser',

            'description' => 'TheSeer\\phpDox\\DocBlock\\DescriptionParser',
            'param'       => 'TheSeer\\phpDox\\DocBlock\\ParamParser',
            'var'         => 'TheSeer\\phpDox\\DocBlock\\VarParser',
            'return'      => 'TheSeer\\phpDox\\DocBlock\\VarParser',
            'license'     => 'TheSeer\\phpDox\\DocBlock\\LicenseParser',

            'internal' => 'TheSeer\\phpDox\\DocBlock\\InternalParser'
        ];

        return [
            'GenericElement by name from elementMap'        => [
                'TheSeer\\phpDox\\DocBlock\\GenericElement',
                'generic',
                $elementMap
            ],
            'GenericElement by unkown name from elementMap' => [
                'TheSeer\\phpDox\\DocBlock\\GenericElement',
                'Tux',
                $elementMap
            ],
            'InvalidElement by name from elementMap'        => [
                'TheSeer\\phpDox\\DocBlock\\InvalidElement',
                'invalid',
                $elementMap
            ],
            'InvalidParser by name from parserMap'          => [
                'TheSeer\\phpDox\\DocBlock\\InvalidParser',
                'invalid',
                $parserMap
            ],
            'GenericParser by unkown name from parserMap'   => [
                'TheSeer\\phpDox\\DocBlock\\GenericParser',
                'Tux',
                $parserMap
            ],
        ];
    }

    /**
     * @covers \TheSeer\phpDox\DocBlock\Factory::getElementInstanceFor
     *
     * @uses   \TheSeer\phpDox\DocBlock\GenericElement
     */
    public function testGetElementInstanceFor(): void {
        $factory = new Factory();
        $this->assertInstanceOf(
            'TheSeer\\phpDox\\DocBlock\\GenericElement',
            $factory->getElementInstanceFor('Tux')
        );
    }

    /**
     * @covers TheSeer\phpDox\DocBlock\Factory::getParserInstanceFor
     *
     * @uses   TheSeer\phpDox\DocBlock\GenericParser
     */
    public function testGetParserInstanceFor(): void {
        $factory = new Factory();
        $this->assertInstanceOf(
            'TheSeer\\phpDox\\DocBlock\\GenericParser',
            $factory->getParserInstanceFor('Tux')
        );
    }

    /**
     * @dataProvider getInstanceMapDataprovider
     * @covers       TheSeer\phpDox\DocBlock\Factory::getInstanceByMap
     *
     * @uses         TheSeer\phpDox\Tests\Integration\DocBlock\FactoryProxy
     * @uses         TheSeer\fDOM\fDOMDocument
     * @uses         TheSeer\fDOM\fDOMElement
     * @uses         TheSeer\phpDox\DocBlock\GenericElement
     * @uses         TheSeer\phpDox\DocBlock\InvalidElement
     * @uses         TheSeer\phpDox\DocBlock\InvalidParser
     * @uses         TheSeer\phpDox\DocBlock\GenericParser
     * @uses         TheSeer\phpDox\DocBlock\DescriptionParser
     * @uses         TheSeer\phpDox\DocBlock\ParamParser
     * @uses         TheSeer\phpDox\DocBlock\VarParser
     * @uses         TheSeer\phpDox\DocBlock\VarParser
     * @uses         TheSeer\phpDox\DocBlock\LicenseParser
     * @uses         TheSeer\phpDox\DocBlock\InternalParser
     */
    public function testGetInstanceByMap($expected, $name, $elementMap): void {
        $factory = new FactoryProxy();
        $this->assertInstanceOf(
            $expected,
            $factory->getInstanceByMap($elementMap, $name)
        );
    }

    /**
     * @covers TheSeer\phpDox\DocBlock\Factory::getInstanceByMap
     *
     * @uses   TheSeer\phpDox\Tests\Integration\DocBlock\FactoryProxy
     */
    public function testGetInstanceByMapHandlingAFactory(): void {
        $factoryMock = $this->createMock(FactoryInterface::class);
        $factoryMock
            ->expects($this->once())
            ->method('getInstanceFor')
            ->will($this->returnValue(new \stdClass));

        $factory = new FactoryProxy();

        $this->assertInstanceOf(
            '\stdClass',
            $factory->getInstanceByMap(['GnuFactory' => $factoryMock], 'GnuFactory')
        );
    }
}

class FactoryProxy extends Factory {
    public function getInstanceByMap($map, $name, $annotation = null) {
        return parent::getInstanceByMap($map, $name, $annotation);
    }
}
