<?php declare(strict_types = 1);
namespace TheSeer\phpDox;

use TheSeer\fDOM\fDOMDocument;

/**
 * Class GlobalConfigTest
 *
 * @covers \TheSeer\phpDox\GlobalConfig
 *
 * @uses   \TheSeer\fDom\fDOMDocument
 * @uses   \TheSeer\phpDox\FileInfo
 * @uses   \TheSeer\phpDox\Version
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

    public function __construct($name = null, array $data = [], $dataName = '') {
        parent::__construct($name, $data, $dataName);
        $this->baseDir = \realpath(__DIR__ . '/../../data/config') . '/';
    }

    /**
     * @expectedException \TheSeer\phpDox\ConfigException
     * @expectedExceptionCode \TheSeer\phpDox\ConfigException::InvalidDataStructure
     */
    public function testTryingToLoadInvalidConfigThrowsException(): void {
        $this->init('broken');
    }

    public function testConfigFileCanBeRetrieved(): void {
        $this->init('empty');
        $this->assertSame(
            $this->fileInfo,
            $this->config->getConfigFile()
        );
    }

    public function testSilentModeDefaultsToFalse(): void {
        $this->init('empty');
        $this->assertFalse($this->config->isSilentMode());
    }

    public function testSilentModeCanBeEnabled(): void {
        $this->init('empty');
        $this->cfgDom->documentElement->setAttribute('silent', 'true');
        $this->assertTrue($this->config->isSilentMode());
    }

    public function testSilentModeCanBeDisabled(): void {
        $this->init('empty');
        $this->cfgDom->documentElement->setAttribute('silent', 'false');
        $this->assertFalse($this->config->isSilentMode());
    }

    /**
     * @uses TheSeer\phpDox\FileInfoCollection
     */
    public function testGetCustomBootstrapFilesReturnsEmptyCollectionByDefault(): void {
        $this->init('empty');
        $result = $this->config->getCustomBootstrapFiles();
        $this->assertInstanceOf('TheSeer\\phpDox\\FileInfoCollection', $result);
        $this->assertEmpty($result);
    }

    /**
     * @uses TheSeer\phpDox\FileInfoCollection
     */
    public function testCustomBootstrapFilesCanBeRetrieved(): void {
        $this->init('bootstrap');
        $result = $this->config->getCustomBootstrapFiles();
        $this->assertCount(2, $result);
        $expected = ['/path/to/fileA.php', '/path/to/fileB.php'];

        foreach ($result as $pos => $entry) {
            $this->assertEquals($expected[$pos], $entry->getPathname());
        }
    }

    /**
     * @uses TheSeer\phpDox\ProjectConfig
     * @uses TheSeer\phpDox\Version
     */
    public function testNamedProjectlistCanBeRetrieved(): void {
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
    public function testIndexedProjectListCanBeRetrieved(): void {
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
    public function testProjectListDoesNotIncludeDisabledProjects(): void {
        $this->init('disabled-projects');
        $results = $this->config->getProjects();
        $this->assertCount(1, $results);
        $this->assertInstanceOf('TheSeer\\phpDox\\ProjectConfig', $results['a']);
    }

    /**
     * @dataProvider resolverSrcProvider
     *
     * @uses         TheSeer\phpDox\ProjectConfig
     * @uses         TheSeer\phpDox\Version
     */
    public function testSourceVariableGetsResolvedCorrectly($expected, $file): void {
        $this->init('resolver/' . $file);
        /** @var ProjectConfig[] $projects */
        $projects = $this->config->getProjects();
        $this->assertEquals($expected, \current($projects)->getWorkDirectory()->getPathname());
    }

    public function resolverSrcProvider() {
        return [
            'phpDox.project.source'            => ['source', 'src'],
            'phpDox.project.source[undefined]' => ['src', 'src-undefined'],
        ];
    }

    /**
     * @dataProvider resolverProvider
     *
     * @uses         TheSeer\phpDox\ProjectConfig
     * @uses         TheSeer\phpDox\Version
     */
    public function testVariablesGetResolvedCorrectly($expected, $file): void {
        $this->init('resolver/' . $file);
        /** @var ProjectConfig[] $projects */
        $projects = $this->config->getProjects();
        $this->assertEquals($expected, \current($projects)->getSourceDirectory()->getPathname());
    }

    public function resolverProvider() {
        $version = new Version('0.0');

        return [
            'basedir' => [$this->baseDir . 'resolver', 'basedir'],

            'phpDox.home'    => ['/tmp', 'home'],
            'phpDox.file'    => [$this->baseDir . 'resolver/file.xml', 'file'],
            'phpDox.version' => [$version->getVersion(), 'phpdox-version'],

            'multi' => ['/tmp ' . $version->getVersion(), 'multi'],

            'phpDox.project.name'            => ['projectname', 'named'],
            'phpDox.project.name[undefined]' => ['unnamed', 'named-undefined'],

            'phpDox.project.workdir'            => ['output', 'workdir'],
            'phpDox.project.workdir[undefined]' => ['xml', 'workdir-undefined'],

            'phpDox.php.version' => [\PHP_VERSION, 'php-version'],

            'property-global'  => ['propvalue', 'property-global'],
            'property-project' => ['propvalue', 'property-project'],

            'property-recursive'           => [$version->getVersion(), 'property-recursive'],
            'property-recursive-recursive' => [$this->baseDir . 'resolver/xml', 'property-recursive-recursive']
        ];
    }

    /**
     * @dataProvider exceptionProvider
     *
     * @uses         TheSeer\phpDox\Version
     */
    public function testInvalidPropertyRequestThrowsException($file, $code): void {
        $this->init('resolver/' . $file);
        $this->expectException('TheSeer\\phpDox\\ConfigException', $code);
        $this->config->getProjects();
    }

    public function exceptionProvider() {
        return [
            'property-internal'  => ['property-internal', ConfigException::OverrideNotAllowed],
            'property-overwrite' => ['property-overwrite', ConfigException::OverrideNotAllowed],
            'property-undefined' => ['property-undefined', ConfigException::PropertyNotFound]
        ];
    }

    private function init($cfgName): void {
        $this->version  = new Version('0.0');
        $this->fileInfo = new FileInfo($this->baseDir . $cfgName . '.xml');
        $this->cfgDom   = new fDOMDocument();
        $this->cfgDom->load($this->fileInfo->getPathname());
        $this->cfgDom->registerNamespace('cfg', 'http://xml.phpdox.net/config');
        $this->config = new GlobalConfig($this->version, new FileInfo('/tmp'), $this->cfgDom, $this->fileInfo);
    }
}
