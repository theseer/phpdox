<?php
/**
 * Copyright (c) 2010-2014 Arne Blankerts <arne@blankerts.de>
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
 */
namespace TheSeer\phpDox {

    use TheSeer\DirectoryScanner\DirectoryScanner;
    use TheSeer\phpDox\Collector\InheritanceResolver;
    use TheSeer\phpDox\Generator\Enricher\EnricherException;

    /**
     * The main Application class
     *
     * @author     Arne Blankerts <arne@blankerts.de>
     * @copyright  Arne Blankerts <arne@blankerts.de>, All rights reserved.
     * @license    BSD License
     * @link       http://phpDox.de
     */
    class Application {

        /**
         * Logger for progress and error reporting
         *
         * @var ProgressLogger
         */
        private $logger;

        /**
         * Factory instance
         * @var Factory
         */
        private $factory;

        /**
         * Constructor of PHPDox Application
         *
         * @param Factory   $factory   Factory instance
         * @param ProgressLogger $logger Instance of the ProgressLogger class
         */
        public function __construct(Factory $factory, ProgressLogger $logger) {
            $this->factory = $factory;
            $this->logger = $logger;
        }

        /**
         * Run Bootstrap code for given list of bootstrap files
         *
         * @param array $requires
         *
         * @return Bootstrap
         */
        public function runBootstrap(array $requires) {
            $bootstrap = $this->factory->getInstanceFor('Bootstrap');
            $bootstrap->load($requires);
            return $bootstrap;
        }

        /**
         * Run collection process on given directory tree
         *
         * @param CollectorConfig $config Configuration options
         *
         * @throws ApplicationException
         * @return void
         */
        public function runCollector(CollectorConfig $config) {
            $this->logger->log("Starting collector");

            $srcDir = $config->getSourceDirectory();
            if (!$srcDir->isDir()) {
                throw new ApplicationException(
                    sprintf('Invalid src directory "%s" specified', $srcDir),
                    ApplicationException::InvalidSrcDirectory
                );
            }
            $xmlDir = $config->getWorkDirectory();

            /** @var $scanner DirectoryScanner */
            $scanner = $this->factory->getInstanceFor(
                    'Scanner',
                    $config->getIncludeMasks(),
                    $config->getExcludeMasks()
            );
            $scanner->setFlag(\FilesystemIterator::UNIX_PATHS);

            $collector = $this->factory->getInstanceFor('Collector',
                $srcDir,
                $xmlDir,
                $config->isPublicOnlyMode()
            );

            $backend = $this->factory->getInstanceFor('BackendFactory')->getInstanceFor($config->getBackend());
            $project = $collector->run($scanner, $backend);

            if ($collector->hasParseErrors()) {
                $this->logger->log('The following file(s) had errors during processing and were excluded:');
                foreach($collector->getParseErrors() as $file => $message) {
                    $this->logger->log(' - ' . $file . ' (' . $message . ')');
                }
            }

            $this->logger->log("Saving results to directory '{$xmlDir}'");
            $vanished = $project->cleanVanishedFiles();
            if (count($vanished) > 0) {
                $this->logger->log(sprintf("Removed %d vanished file(s) from project:", count($vanished)));
                foreach($vanished as $file) {
                    $this->logger->log(' - ' . $file);
                }
            }
            $changed = $project->save();
            if ($config->doResolveInheritance()) {
                /** @var $resolver InheritanceResolver */
                $resolver = $this->factory->getInstanceFor('InheritanceResolver');
                $resolver->resolve($changed, $project, $config->getInheritanceConfig());

                if ($resolver->hasUnresolved()) {
                    $this->logger->log('The following unit(s) had missing dependencies during inheritance resolution:');
                    foreach($resolver->getUnresolved() as $class => $missing) {
                        if (is_array($missing)) {
                            $missing = join(', ', $missing);
                        }
                        $this->logger->log(' - ' . $class . ' (missing ' . $missing . ')');
                    }
                }
            }
            $this->logger->log("Collector process completed\n");
        }

        /**
         * Run Documentation generation process
         *
         * @param GeneratorConfig $config
         *
         * @throws ApplicationException
         * @return void
         */
        public function runGenerator(GeneratorConfig $config) {
            $this->logger->reset();
            $this->logger->log("Starting generator");

            $engineFactory = $this->factory->getInstanceFor('EngineFactory');
            $enricherFactory = $this->factory->getInstanceFor('EnricherFactory');

            $failed = array_diff($config->getRequiredEngines(), $engineFactory->getEngineList());
            if (count($failed)) {
               $list = join("', '", $failed);
               throw new ApplicationException("The engine(s) '$list' is/are not registered", ApplicationException::UnknownEngine);
            }

            $failed = array_diff($config->getRequiredEnrichers(), $enricherFactory->getEnricherList());
            if (count($failed)) {
                $list = join("', '", $failed);
                throw new ApplicationException("The enricher(s) '$list' is/are not registered", ApplicationException::UnknownEnricher);
            }

            $generator = $this->factory->getInstanceFor('Generator');

            foreach($config->getActiveBuilds() as $buildCfg) {
                $generator->addEngine( $engineFactory->getInstanceFor($buildCfg) );
            }

            $this->logger->log('Loading enrichers');
            foreach($config->getActiveEnrichSources() as $type => $enrichCfg) {
                try {
                    $enricher = $enricherFactory->getInstanceFor($enrichCfg);
                    $generator->addEnricher($enricher);
                    $this->logger->log(
                        sprintf('Enricher %s initialized successfully', $enricher->getName())
                    );
                } catch (EnricherException $e) {
                    $this->logger->log(
                        sprintf("Exception while initializing enricher %s:\n\n    %s\n",
                            $type,
                            $e->getMessage()
                        )
                    );
                }
            }

            $pconfig = $config->getProjectConfig();

            if (!file_exists($pconfig->getWorkDirectory() . '/index.xml')) {
                throw new ApplicationException(
                    'Workdirectory does not contain an index.xml file. Did you run the collector?',
                    ApplicationException::IndexMissing
                );
            }

            if (!file_exists($pconfig->getWorkDirectory() . '/source.xml')) {
                throw new ApplicationException(
                    'Workdirectory does not contain an source.xml file. Did you run the collector?',
                    ApplicationException::SourceMissing
                );
            }

            $srcDir = $pconfig->getSourceDirectory();
            if (!file_exists($srcDir) || !is_dir($srcDir)) {
                throw new ApplicationException(
                    sprintf('Invalid src directory "%s" specified', $srcDir),
                    ApplicationException::InvalidSrcDirectory
                );
            }

            $this->logger->log("Starting event loop.\n");
            $generator->run(
                new \TheSeer\phpDox\Generator\Project(
                    $srcDir,
                    $pconfig->getWorkDirectory()
                )
            );
            $this->logger->log("Generator process completed");
        }

    }

    class ApplicationException extends \Exception {
        const InvalidSrcDirectory = 1;
        const UnknownEngine = 2;
        const UnknownEnricher = 3;
        const IndexMissing = 4;
        const SourceMissing = 5;
    }
}
