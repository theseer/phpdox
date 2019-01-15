<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Collector;

use TheSeer\DirectoryScanner\DirectoryScanner;
use TheSeer\phpDox\Collector\Backend\BackendInterface;
use TheSeer\phpDox\Collector\Backend\ParseErrorException;
use TheSeer\phpDox\FileInfo;
use TheSeer\phpDox\ProgressLogger;

/**
 * Collector processing class
 */
class Collector {
    /**
     * @var ProgressLogger
     */
    private $logger;

    /**
     * @var Project
     */
    private $project;

    /**
     * @var array
     */
    private $parseErrors = [];

    /**
     * @var BackendInterface
     */
    private $backend;

    /**
     * @var string
     */
    private $encoding;

    /**
     * @var bool
     */
    private $publicOnly;

    /**
     * @param string $encoding
     * @param bool   $publicOnly
     */
    public function __construct(ProgressLogger $logger, Project $project, BackendInterface $backend, $encoding, $publicOnly) {
        $this->logger     = $logger;
        $this->project    = $project;
        $this->backend    = $backend;
        $this->encoding   = $encoding;
        $this->publicOnly = $publicOnly;
    }

    public function run(DirectoryScanner $scanner): Project {
        $srcDir = $this->project->getSourceDir();
        $this->logger->log("Scanning directory '{$srcDir}' for files to process\n");

        $iterator = new SourceFileIterator($scanner($srcDir), $srcDir, $this->encoding);

        foreach ($iterator as $file) {
            $needsProcessing = $this->project->addFile($file);

            if (!$needsProcessing) {
                $this->logger->progress('cached');

                continue;
            }

            if (!$this->processFile($file)) {
                $this->project->removeFile($file);
            }
        }
        $this->logger->completed();

        return $this->project;
    }

    public function hasParseErrors(): bool {
        return \count($this->parseErrors) > 0;
    }

    public function getParseErrors(): array {
        return $this->parseErrors;
    }

    /**
     * @param FileInfo $file
     *
     * @throws CollectorException
     * @throws \TheSeer\phpDox\ProgressLoggerException
     */
    private function processFile(SourceFile $file): bool {
        try {
            if ($file->getSize() === 0) {
                $this->logger->progress('processed');

                return true;
            }
            $result = $this->backend->parse($file, $this->publicOnly);

            if ($result->hasClasses()) {
                foreach ($result->getClasses() as $class) {
                    $this->project->addClass($class);
                }
            }

            if ($result->hasInterfaces()) {
                foreach ($result->getInterfaces() as $interface) {
                    $this->project->addInterface($interface);
                }
            }

            if ($result->hasTraits()) {
                foreach ($result->getTraits() as $trait) {
                    $this->project->addTrait($trait);
                }
            }
            $this->logger->progress('processed');

            return true;
        } catch (ParseErrorException $e) {
            $previous                                = $e->getPrevious();
            $this->parseErrors[$file->getPathname()] = \sprintf(
                '%s [%s:%d]',
                $previous->getMessage(),
                \basename($previous->getFile()),
                $previous->getLine()
            );
            $this->logger->progress('failed');

            return false;
        } catch (\Exception $e) {
            throw new CollectorException('Error while processing source file', CollectorException::ProcessingError, $e, $file);
        }
    }
}
