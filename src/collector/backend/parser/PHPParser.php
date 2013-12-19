<?php
    /**
     * Copyright (c) 2010-2013 Arne Blankerts <arne@blankerts.de>
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
     * @author     Arne Blankerts <arne@blankerts.de>
     * @copyright  Arne Blankerts <arne@blankerts.de>, All rights reserved.
     * @license    BSD License
     */
namespace TheSeer\phpDox\Collector\Backend {

    use TheSeer\phpDox\Collector\SourceFile;
    use \TheSeer\phpDox\DocBlock\Parser as DocblockParser;

    /**
     *
     */
    class PHPParser implements BackendInterface {

        /**
         * @var \PHPParser_Parser
         */
        private $parser = NULL;

        /**
         * @var \TheSeer\phpDox\DocBlock\Parser
         */
        private $docblockParser = NULL;

        private $traverser = NULL;


        public function __construct(DocblockParser $parser) {
            $this->docblockParser = $parser;
        }

        /**
         *
         * @param SourceFile $sourceFile
         *
         * @throws ParseErrorException
         * @return ParseResult
         */
        public function parse(SourceFile $sourceFile) {
            try {
                $result = new ParseResult($sourceFile->getFileInfo());
                $parser = $this->getParserInstance();
                $nodes = $parser->parse($sourceFile->getSource());
                $this->getTraverserInstance($result)->traverse($nodes);
                return $result;
            } catch (\Exception $e) {
                throw new ParseErrorException('Something went wrwong', ParseErrorException::GeneralParseError, $e);
            }
        }

        /**
         * @return \PHPParser_Parser
         */
        private function getParserInstance() {
            if ($this->parser === NULL) {
                $this->parser = new \PHPParser_Parser(new OriginalValueLexer());
            }
            return $this->parser;
        }

        /**
         * @return \PHPParser_NodeTraverser
         */
        private function getTraverserInstance(ParseResult $result) {
            $traverser = new \PHPParser_NodeTraverser();
            $traverser->addVisitor(new \PHPParser_NodeVisitor_NameResolver());
            $traverser->addVisitor(new UnitCollectingVisitor($this->docblockParser, $result));
            return $traverser;
        }
    }

}