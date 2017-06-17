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
 * @author     Arne Blankerts <arne@blankerts.de>
 * @copyright  Arne Blankerts <arne@blankerts.de>, All rights reserved.
 * @license    BSD License
 */

namespace TheSeer\phpDox\Tests\Integration\DocBlock {

    use TheSeer\fDOM\fDOMDocument;
    use TheSeer\phpDox\DocBlock\Factory;
    use TheSeer\phpDox\DocBlock\Parser;

    /**
     * Class ParserTest
     *
     * @ uses TheSeer\phpDox\DocBlock\Factory
     * @ covers TheSeer\phpDox\DocBlock\Parser
     */
    class ParserTest extends \PHPUnit\Framework\TestCase {

        /**
         * @ covers TheSeer\phpDox\DocBlock\Parser::__construct
         * @ covers TheSeer\phpDox\DocBlock\Parser::parse
         *
         * @dataProvider docblockSources
         */
        public function testParse($src) {
            $expected = new fDOMDocument();
            $dir = __DIR__.'/../../data/docbock/';
            $block = file_get_contents($dir . $src);
            $expected->load($dir . $src . '.xml');

            $factory = new Factory();
            $parser = new Parser($factory);
            $result = $parser->parse($block, array());

            $this->assertInstanceOf('TheSeer\\phpDox\\DocBlock\\DocBlock', $result);

            $dom = new fDOMDocument();
            $dom->appendChild($result->asDom($dom));

            $this->assertEquals($expected->documentElement, $dom->documentElement);
        }

        public function docblockSources() {
            return array(
                array('author'),
                array('body'),
                array('complex'),
                array('deprecated'),
                array('docblock'),
                array('docblock_compact'),
                array('docblock_compact_multiline_short'),
                array('empty'),
                array('exception'),
                array('global'),
                array('heading'),
                array('multiat'),
                array('multiline_body'),
                array('param_without_description'),
                array('param_without_varname'),
                array('param_without_varname_and_description'),
                array('see'),
                array('since'),
                array('throws'),
                array('var_full'),
                array('var_no_body'),
                array('var_only'),
                array('var_only_single_line'),
                array('version')
            );
        }
    }
}
