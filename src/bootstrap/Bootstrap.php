<?php declare(strict_types = 1);
namespace TheSeer\phpDox;

class Bootstrap {
    public function __construct(ProgressLogger $logger, BootstrapApi $api) {
        $this->logger = $logger;
        $this->api    = $api;
    }

    /**
     * Load bootstrap files to register components and builder
     *
     * @param FileInfoCollection $require list of files to require
     *
     * @throws BootstrapException
     */
    public function load(FileInfoCollection $require, $silent = true): void {
        foreach ($require as $file) {
            /** @var FileInfo $file */
            if (!$file->exists()) {
                throw new BootstrapException(
                    \sprintf("Require file '%s' not found or not a file", $file->getRealPath()),
                    BootstrapException::RequireFailed
                );
            }

            if (!$silent) {
                $this->logger->log(
                    \sprintf("Loading bootstrap file '%s'", $file->getRealPath())
                );
            }
            $this->loadBootstrap($file);
        }
    }

    public function getBackends() {
        return $this->api->getBackends();
    }

    public function getEngines() {
        return $this->api->getEngines();
    }

    public function getEnrichers() {
        return $this->api->getEnrichers();
    }

    private function loadBootstrap($filename): void {
        /** @noinspection PhpUnusedLocalVariableInspection */
        $phpDox = $this->api;
        /** @noinspection PhpIncludeInspection */
        require $filename;
    }
}
