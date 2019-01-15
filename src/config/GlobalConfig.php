<?php declare(strict_types = 1);
namespace TheSeer\phpDox;

use TheSeer\fDOM\fDOMDocument;

class GlobalConfig {
    /**
     * @var Version
     */
    private $version;

    /**
     * Directory of phpDox home
     *
     * @var Fileinfo
     */
    private $homeDir;

    /**
     * @var fDOMDocument
     */
    private $cfg;

    /**
     * File this config is based on
     *
     * @var FileInfo
     */
    private $file;

    /**
     * Constructor for global config
     *
     * @param fDOMDocument $cfg  A configuration dom
     * @param FileInfo     $file FileInfo of the cfg file
     *
     * @throws ConfigException
     */
    public function __construct(Version $version, FileInfo $home, fDOMDocument $cfg, FileInfo $file) {
        if ($cfg->documentElement->nodeName != 'phpdox' ||
            $cfg->documentElement->namespaceURI != 'http://xml.phpdox.net/config') {
            throw new ConfigException('Not a valid phpDox configuration', ConfigException::InvalidDataStructure);
        }
        $this->homeDir = $home;
        $this->version = $version;
        $this->cfg     = $cfg;
        $this->file    = $file;
    }

    public function getConfigFile(): FileInfo {
        return $this->file;
    }

    public function isSilentMode(): bool {
        $root = $this->cfg->queryOne('/cfg:phpdox');

        return $root->getAttribute('silent', 'false') === 'true';
    }

    public function getCustomBootstrapFiles(): FileInfoCollection {
        $files = new FileInfoCollection();

        foreach ($this->cfg->query('//cfg:bootstrap/cfg:require[@file]') as $require) {
            $files->add(new FileInfo($require->getAttribute('file')));
        }

        return $files;
    }

    public function getProjects(): array {
        $list = [];

        foreach ($this->cfg->query('//cfg:project[@enabled="true" or not(@enabled)]') as $pos => $project) {
            $list[$project->getAttribute('name', $pos)] = new ProjectConfig($this->version, $this->homeDir, $this->runResolver($project));
        }

        return $list;
    }

    /**
     * @param $ctx
     *
     * @throws ConfigException
     */
    private function runResolver($ctx) {
        $vars = [
            'basedir' => $ctx->getAttribute('basedir', \dirname($this->file->getRealPath())),

            'phpDox.home'    => $this->homeDir->getPathname(),
            'phpDox.file'    => $this->file->getPathname(),
            'phpDox.version' => $this->version->getVersion(),

            'phpDox.project.name'    => $ctx->getAttribute('name', 'unnamed'),
            'phpDox.project.source'  => $ctx->getAttribute('source', 'src'),
            'phpDox.project.workdir' => $ctx->getAttribute('workdir', 'xml'),

            'phpDox.php.version' => \PHP_VERSION,

        ];
        $protected = \array_keys($vars);

        foreach ($ctx->query('cfg:property|/cfg:phpdox/cfg:property') as $property) {
            /** @var $property \DOMElement */
            $name = $property->getAttribute('name');
            $line = $property->getLineNo();

            if (\in_array($name, $protected)) {
                throw new ConfigException("Cannot overwrite system property in line $line", ConfigException::OverrideNotAllowed);
            }

            if (isset($vars[$name])) {
                throw new ConfigException("Cannot overwrite existing property '$name' in line $line", ConfigException::OverrideNotAllowed);
            }
            $vars[$name] = $this->resolveValue($property->getAttribute('value'), $vars, $line);
        }

        foreach ($ctx->query('.//*[not(name()="property")]/@*|@*') as $attr) {
            $attr->nodeValue = $this->resolveValue($attr->nodeValue, $vars, $attr->getLineNo());
        }

        return $ctx;
    }

    /**
     * @param string   $value
     * @param string[] $vars
     * @param int      $line
     */
    private function resolveValue($value, array $vars, $line): string {
        $result = \preg_replace_callback(
            '/\${(.*?)}/',
            function ($matches) use ($vars, $line) {
                if (!isset($vars[$matches[1]])) {
                    throw new ConfigException("No value for property '{$matches[1]}' found in line $line", ConfigException::PropertyNotFound);
                }

                return $vars[$matches[1]];
            },
            $value
        );

        if (\preg_match('/\${(.*?)}/', $result)) {
            $result = $this->resolveValue($result, $vars, $line);
        }

        return $result;
    }
}
