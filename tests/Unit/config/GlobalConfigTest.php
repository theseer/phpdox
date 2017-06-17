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

    /**
     * Class GlobalConfigTest
     *
     * @covers TheSeer\phpDox\GlobalConfig
     * @uses TheSeer\fDom\fDOMDocument
     * @uses TheSeer\phpDox\FileInfo
     * @uses TheSeer\phpDox\Version
     */
    class GlobalConfigTest extends \PHPUnit\Framework\TestCase {

        private $baseDir;

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

        /**
         * @var Version
         */
        private $version;

        public function __construct($name = NULL, array $data = array(), $dataName = '') {
            parent::__construct($name, $data, $dataName);
            $this->baseDir = realpath(__DIR__ . '/../../data/config') . '/';
        }

        private function init($cfgName) {
            $this->version = new Version('0.0');
            $this->fileInfo = new FileInfo($this->baseDir . $cfgName . '.xml');
            $this->cfgDom = new fDOMDocument();
            $this->cfgDom->load($this->fileInfo->getPathname());
            $this->cfgDom->registerNamespace('cfg', 'http://xml.phpdox.net/config');
            $this->config = new GlobalConfig($this->version, new FileInfo('/tmp'), $this->cfgDom, $this->fileInfo);
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

        /**
         * @uses TheSeer\phpDox\FileInfoCollection
         */
        public function testGetCustomBootstrapFilesReturnsEmptyCollectionByDefault() {
            $this->init('empty');
            $result = $this->config->getCustomBootstrapFiles();
            $this->assertInstanceOf('TheSeer\\phpDox\\FileInfoCollection', $result);
            $this->assertEmpty($result);
        }

        /**
         * @uses TheSeer\phpDox\FileInfoCollection
         */
        public function testCustomBootstrapFilesCanBeRetrieved() {
            $this->init('bootstrap');
            $result = $this->config->getCustomBootstrapFiles();
            $this->assertCount(2, $result);
            $expected = array('/path/to/fileA.php', '/path/to/fileB.php');
            foreach($result as $pos => $entry) {
                $this->assertEquals($expected[$pos], $entry->getPathname());
            }
        }

        /**
         * @uses TheSeer\phpDox\ProjectConfig
         * @uses TheSeer\phpDox\Version
         */
        public function testNamedProjectlistCanBeRetrieved() {
            $this->init('named-projects');
            $results = $this->config->getProjects();
            $this->assertCount(2, $results);
            $this->assertInstanceOf('TheSeer\\phpDox\\ProjectConfig', $results['a']);
            $this->assertInstanceOf('TheSeer\\phpDox\\ProjectConfig', $results['b']);
        }

        /**
         * @uses TheSeer\phpDox\ProjectConfig
         * @uses TheSeer\phpDox\Version
         */
        public function testIndexedProjectListCanBeRetrieved() {
            $this->init('indexed-projects');
            $results = $this->config->getProjects();
            $this->assertCount(2, $results);
            $this->assertInstanceOf('TheSeer\\phpDox\\ProjectConfig', $results[0]);
            $this->assertInstanceOf('TheSeer\\phpDox\\ProjectConfig', $results[1]);
        }

        /**
         * @uses TheSeer\phpDox\ProjectConfig
         * @uses TheSeer\phpDox\Version
         */
            public function testProjectListDoesNotIncludeDisabledProjects() {
            $this->init('disabled-projects');
            $results = $this->config->getProjects();
            $this->assertCount(1, $results);
            $this->assertInstanceOf('TheSeer\\phpDox\\ProjectConfig', $results['a']);
        }

        /**
         * @dataProvider resolverSrcProvider
         * @uses TheSeer\phpDox\ProjectConfig
         * @uses TheSeer\phpDox\Version
         */
        public function testSourceVariableGetsResolvedCorrectly($expected, $file) {
            $this->init('resolver/' .  $file);
            /** @var ProjectConfig[] $projects */
            $projects = $this->config->getProjects();
            $this->assertEquals($expected, current($projects)->getWorkDirectory()->getPathname());
        }

        public function resolverSrcProvider() {
            return array(
                'phpDox.project.source' => array('source','src'),
                'phpDox.project.source[undefined]' => array('src','src-undefined'),
            );
        }

        /**
         * @dataProvider resolverProvider
         * @uses TheSeer\phpDox\ProjectConfig
         * @uses TheSeer\phpDox\Version
         */
        public function testVariablesGetResolvedCorrectly($expected, $file) {
            $this->init('resolver/' .  $file);
            /** @var ProjectConfig[] $projects */
            $projects = $this->config->getProjects();
            $this->assertEquals($expected, current($projects)->getSourceDirectory()->getPathname());
        }

        public function resolverProvider() {
            $version = new Version('0.0');
            return array(
                'basedir' => array( $this->baseDir . 'resolver', 'basedir'),

                'phpDox.home' => array( '/tmp', 'home'),
                'phpDox.file' => array( $this->baseDir . 'resolver/file.xml', 'file'),
                'phpDox.version' => array($version->getVersion(), 'phpdox-version'),

                'multi' => array( '/tmp ' . $version->getVersion(), 'multi'),

                'phpDox.project.name' => array('projectname', 'named'),
                'phpDox.project.name[undefined]' => array('unnamed', 'named-undefined'),

                'phpDox.project.workdir' => array('output','workdir'),
                'phpDox.project.workdir[undefined]' => array('xml','workdir-undefined'),

                'phpDox.php.version' => array(PHP_VERSION, 'php-version'),

                'property-global' => array('propvalue', 'property-global'),
                'property-project' => array('propvalue', 'property-project'),

                'property-recursive' => array($version->getVersion(), 'property-recursive'),
                'property-recursive-recursive' => array( $this->baseDir . 'resolver/xml', 'property-recursive-recursive')
            );
        }

        /**
         * @dataProvider exceptionProvider
         * @uses TheSeer\phpDox\Version
         */
        public function testInvalidPropertyRequestThrowsException($file, $code) {
            $this->init('resolver/' .  $file);
            $this->expectException('TheSeer\\phpDox\\ConfigException', $code);
            $this->config->getProjects();
        }

        public function exceptionProvider() {
            return array(
                'property-internal' => array('property-internal', ConfigException::OverrideNotAllowed),
                'property-overwrite' => array('property-overwrite', ConfigException::OverrideNotAllowed),
                'property-undefined' => array('property-undefined', ConfigException::PropertyNotFound)
            );
        }

    }
}
