<?php declare(strict_types = 1);
namespace TheSeer\phpDox;

class Version {
    /**
     * @var string
     */
    private $release;

    /**
     * @var string
     */
    private $version;

    public function __construct($release) {
        $this->release = $release;
    }

    public function __toString() {
        return $this->getInfoString();
    }

    public function getVersion(): string {
        if ($this->version == null) {
            $this->version = $this->initialize();
        }

        return $this->version;
    }

    public function getInfoString() {
        return 'phpDox ' . $this->getVersion() . ' - Copyright (C) 2010 - ' . \date('Y', \getenv('SOURCE_DATE_EPOCH') ?: \time()) . ' by Arne Blankerts and Contributors';
    }

    public function getGeneratedByString() {
        return 'Generated using ' . $this->getInfoString();
    }

    private function initialize() {
        if (!\is_dir(__DIR__ . '/../../.git') || \strpos(\ini_get('disable_functions'), 'exec') !== false) {
            return $this->release;
        }

        $dir = \getcwd();
        \chdir(__DIR__);

        $devNull = '/dev/null';
        $cmd     = 'command -p git';

        if (\strtolower(\substr(\PHP_OS, 0, 3)) === 'win') {
            $devNull = 'nul';
            $cmd     = 'git.exe';
        }

        $git = \exec($cmd . ' describe --always --dirty 2>' . $devNull, $foo, $rc);
        \chdir($dir);

        if ($rc === 0) {
            return $git;
        }

        return $this->release;
    }
}
