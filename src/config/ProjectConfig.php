<?php declare(strict_types = 1);
namespace TheSeer\phpDox;

use TheSeer\fDOM\fDOMElement;

class ProjectConfig {
    /**
     * @var Version
     */
    private $version;

    /**
     * @var fDOMElement;
     */
    private $ctx;

    /**
     * @var FileInfo
     */
    private $homeDir;

    /**
     * Constructor for global config
     *
     * @param fDOMElement $ctx Reference to <project> node
     */
    public function __construct(Version $version, FileInfo $homeDir, fDOMElement $ctx) {
        $this->version = $version;
        $this->homeDir = $homeDir;
        $this->ctx     = $ctx;
    }

    public function getVersion(): Version {
        return $this->version;
    }

    public function getHomeDirectory(): Fileinfo {
        return $this->homeDir;
    }

    public function getWorkDirectory(): FileInfo {
        return new FileInfo($this->ctx->getAttribute('workdir', 'xml'));
    }

    public function getSourceDirectory(): FileInfo {
        return new FileInfo($this->ctx->getAttribute('source', 'src'));
    }

    public function isPublicOnlyMode(): bool {
        return $this->ctx->getAttribute('publiconly', 'false') === 'true';
    }

    /**
     * @throws ConfigException
     */
    public function getCollectorConfig(): CollectorConfig {
        $colNode = $this->ctx->queryOne('cfg:collector');

        if (!$colNode) {
            throw new ConfigException('Project does not have a collector section', ConfigException::NoCollectorSection);
        }

        return new CollectorConfig($this, $colNode);
    }

    /**
     * @throws ConfigException
     */
    public function getGeneratorConfig(): GeneratorConfig {
        $genNode = $this->ctx->queryOne('cfg:generator');

        if (!$genNode) {
            throw new ConfigException('Project does not have a generator section', ConfigException::NoGeneratorSection);
        }

        return new GeneratorConfig($this, $genNode);
    }
}
