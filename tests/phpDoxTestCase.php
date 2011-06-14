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
 * @package    phpDox
 * @subpackage Tests
 * @author     Bastian Feder <phpdox@bastian-feder.de>
 * @copyright  Arne Blankerts <arne@blankerts.de>, All rights reserved.
 * @license    BSD License
 */

namespace TheSeer\phpDox\Tests {

    class phpDox_TestCase extends \PHPUnit_Framework_TestCase {

        /*********************************************************************/
        /* Fixtures                                                          */
        /*********************************************************************/

        /**
         * Provides a DOMDocument
         *
         * @return \DOMDocument
         */
        protected function getDomDocument() {
            if (!isset($this->doc)) {
                $this->doc = new \DOMDocument();
            }
            return $this->doc;
        }

        /**
         * Provides a stubbed instance of TheSeer\fDOM\fDOMDocument.
         *
         * @param array $methods
         * @return TheSeer\fDOM\fDOMDocument
         */
        protected function getFDomDocumentFixture(array $methods) {

            return $this->getMockBuilder('TheSeer\\fDOM\\fDOMDocument')
                ->disableOriginalConstructor()
                ->setMethods($methods)
                ->getMock();
        }

        /**
         * Provides a stubbed instance of TheSeer\phpDox\DocBlock\Factory.
         *
         * @param array $methods
         * @return TheSeer\phpDox\DocBlock\Factory
         */
        protected function getFactoryFixture(array $methods = array()) {
            return $this->getMockBuilder('TheSeer\\phpDox\\DocBlock\\Factory')
                ->setMethods($methods)
                ->getMock();
        }
    }
}