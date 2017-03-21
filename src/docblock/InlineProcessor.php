<?php
/**
 * Copyright (c) 2010-2017 Arne Blankerts <arne@blankerts.de>
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
 * You can also {@internal let's {@do something}} about it.
 *
 * @package    phpDox
 * @author     Arne Blankerts <arne@blankerts.de>
 * @copyright  Arne Blankerts <arne@blankerts.de>, All rights reserved.
 * @license    BSD License
 */

namespace TheSeer\phpDox\DocBlock {

    class InlineProcessor {

        protected $dom;
        protected $factory;

        protected $regex = '/(.*?)\{(\@(?>[^{}]+|(?R))*)\}|(.*)/m';

        public function __construct(Factory $factory, \TheSeer\fDOM\fDOMDocument $ctx) {
            $this->factory = $factory;
            $this->dom = $ctx;
        }

        public function transformToDom($text) {
            return $this->doParse($text);
        }

        protected function doParse($text) {
            $count = preg_match_all($this->regex, $text, $matches);
            if (join('', $matches[1]) == '') {
                return $this->dom->createTextNode($text);
            }
            $fragment = $this->dom->createDocumentFragment();
            for($x=0; $x<$count; $x++) {
                for($t=1; $t<=3; $t++) {
                    if ($matches[$t][$x] == '') {
                        continue;
                    }
                    if ($t==2) {
                        $fragment->appendChild($this->processMatch($matches[$t][$x]));
                        continue;
                    }
                    $part = $matches[$t][$x];
                    if ($t==3 && $part != '') {
                        $part .= "\n";
                    }
                    $fragment->appendChild($this->dom->createTextNode($part));
                }
            }
            return $fragment;
        }

        protected function processMatch($match) {
            if ($match === '@') {
                return $this->dom->createTextNode('{');
            }
            $parts = preg_split("/[\s,]+/", $match, 2, PREG_SPLIT_NO_EMPTY);
            $annotation = mb_substr($parts[0], 1);
            if (preg_match('=^[a-zA-Z0-9]*$=', $annotation)) {
                $parser = $this->factory->getParserInstanceFor($annotation);
            } else {
                $parser = $this->factory->getParserInstanceFor('invalid', $annotation);
            }
            if (isset($parts[1])) {
                $parser->setPayload($parts[1]);
            }

            $node = $parser->getObject(array())->asDom($this->dom);
            foreach($node->childNodes as $child) {
                if ($child instanceof \DOMText) {
                    $node->replaceChild($this->doParse($child->wholeText), $child);
                }
            }
            return $node;

        }

    }

}
