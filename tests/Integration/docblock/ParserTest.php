<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Tests\Integration\DocBlock;

use PHPUnit\Framework\TestCase;
use TheSeer\fDOM\fDOMDocument;
use TheSeer\phpDox\DocBlock\DocBlock;
use TheSeer\phpDox\DocBlock\Factory;
use TheSeer\phpDox\DocBlock\Parser;

/**
 * Class ParserTest
 *
 * @ uses \TheSeer\phpDox\DocBlock\Factory
 * @ covers \TheSeer\phpDox\DocBlock\Parser
 */
class ParserTest extends TestCase {
    /**
     * @ covers TheSeer\phpDox\DocBlock\Parser::__construct
     * @ covers TheSeer\phpDox\DocBlock\Parser::parse
     *
     * @dataProvider docblockSources
     */
    public function testParse($src): void {
        $expected = new fDOMDocument();
        $dir      = __DIR__ . '/../../data/docbock/';
        $block    = \file_get_contents($dir . $src);
        $expected->load($dir . $src . '.xml');

        $factory = new Factory();
        $parser  = new Parser($factory);
        $result  = $parser->parse($block, []);

        $this->assertInstanceOf(DocBlock::class, $result);

        $dom = new fDOMDocument();
        $dom->appendChild($result->asDom($dom));

        $this->assertEquals($expected->documentElement, $dom->documentElement);
    }

    public function docblockSources() {
        return [
            ['author'],
            ['body'],
            ['complex'],
            ['deprecated'],
            ['docblock'],
            ['docblock_compact'],
            ['docblock_compact_multiline_short'],
            ['empty'],
            ['exception'],
            ['global'],
            ['heading'],
            ['multiat'],
            ['multiline_body'],
            ['param_without_description'],
            ['param_without_varname'],
            ['param_without_varname_and_description'],
            ['see'],
            ['since'],
            ['throws'],
            ['var_full'],
            ['var_no_body'],
            ['var_only'],
            ['var_only_single_line'],
            ['version'],
            ['multiline_annotation']
        ];
    }
}
