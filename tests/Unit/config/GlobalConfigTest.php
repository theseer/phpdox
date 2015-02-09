<?php
/**
 * Copyright (c) 2010-2015 Arne Blankerts <arne@blankerts.de>
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
namespace TheSeer\phpDox {

    use TheSeer\fDOM\fDOMDocument;

    class GlobalConfigTest extends \PHPUnit_Framework_TestCase {

        /**
         * @var FileInfo
         */
        private $fileInfo;

        /**
         * @var fDOMDocument
         */
        private $cfgDom;

        /**
         * @var GlobalConfig
         */
        private $config;

        private function init($cfgName) {
            $this->fileInfo = new FileInfo(__DIR__ . '/../../data/config/' . $cfgName . '.xml');
            $this->cfgDom = new fDOMDocument();
            $this->cfgDom->load($this->fileInfo->getPathname());
            $this->cfgDom->registerNamespace('cfg', 'http://xml.phpdox.net/config');
            $this->config = new GlobalConfig($this->cfgDom, $this->fileInfo);
        }

        /**
         * @expectedException \TheSeer\phpDox\ConfigException
         * @expectedExceptionCode \TheSeer\phpDox\ConfigException::InvalidDataStructure
         */
        public function testTryingToLoadInvalidConfigThrowsException() {
            $this->init('broken');
        }

        public function testConfigFileCanBeRetrieved() {
            $this->init('empty');
            $this->assertSame(
                $this->fileInfo,
                $this->config->getConfigFile()
            );
        }

        public function testSilentModeDefaultsToFalse() {
            $this->init('empty');
            $this->assertFalse($this->config->isSilentMode());
        }

        public function testSilentModeCanBeEnabled() {
            $this->init('empty');
            $this->cfgDom->documentElement->setAttribute('silent', 'true');
            $this->assertTrue($this->config->isSilentMode());
        }

        public function testSilentModeCanBeDisabled() {
            $this->init('empty');
            $this->cfgDom->documentElement->setAttribute('silent', 'false');
            $this->assertFalse($this->config->isSilentMode());
        }

        public function testGetCustomBootstrapFilesReturnsEmptyCollectionByDefault() {
            $this->init('empty');
            $result = $this->config->getCustomBootstrapFiles();
            $this->assertInstanceOf(FileInfoCollection::class, $result);
            $this->assertEmpty($result);
        }

        public function testCustomBootstrapFilesCanBeRetrieved() {
            $this->init('bootstrap');
            $result = $this->config->getCustomBootstrapFiles();
            $this->assertCount(2, $result);
            $expected = array('/path/to/fileA.php', '/path/to/fileB.php');
            foreach($result as $pos => $entry) {
                $this->assertEquals($expected[$pos], $entry->getPathname());
            }
        }
    }
}
