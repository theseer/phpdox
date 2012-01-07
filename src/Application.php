<?php
/**
 * Copyright (c) 2010-2012 Arne Blankerts <arne@blankerts.de>
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
 *
 */
namespace TheSeer\phpDox {

    use \Theseer\DirectoryScanner\IncludeExcludeFilterIterator as Scanner;
    use \TheSeer\fDom\fDomDocument;

    class Application {

        /**
         * Logger for progress and error reporting
         *
         * @var Logger
         */
        protected $logger;

        /**
         * Helper class wrapping container DOMDocuments
         *
         * @var Container
         */
        protected $container = null;

        /**
         * Factory instance
         * @var Factory
         */
        protected $factory;

        /**
         * Map for builder names to generators and configs
         *
         * @var array
         */
        protected $builderMap = array();

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

        public function runBootstrap(array $requires) {
            $bootstrap = $this->factory->getInstanceFor('Bootstrap');
            return $bootstrap->load($requires);
        }

        /**
         * Run collection process on given directory tree
         *
         * @param CollectorConfig  $config     Configuration options
         * @param Scanner          $scanner    A Directory scanner iterator for files/dirs to process
         *
         * @return void
         */
        public function runCollector(CollectorConfig $config) {
            $this->logger->log("Starting collector\n");

            $srcDir = $config->getSourceDirectory();
            $xmlDir = $config->getWorkDirectory();

            $scanner = $this->factory->getInstanceFor(
                    'Scanner',
                    $config->getIncludeMasks(),
                    $config->getExcludeMasks()
            );
            $container = $this->factory->getInstanceFor('container', $xmlDir);

            $collector = $this->factory->getInstanceFor('Collector');
            $collector->setStartIndex(strlen(dirname(realpath($srcDir))));
            $collector->run(
                $scanner($srcDir),
                $container,
                $this->factory->getInstanceFor('Analyser', $config->isPublicOnlyMode()),
                $xmlDir,
                $srcDir
            );

            // enforce existence of all container files, even if the code didn't trigger their creation
            foreach(array('classes','interfaces','namespaces','traits') as $c) {
                $container->getDocument($c);
            }

            $container->save();
            $this->logger->log('Collector process completed');
        }

        /**
         * Run Documentation generation process
         *
         * @return void
         */
        public function runGenerator(GeneratorConfig $config) {
            $this->logger->reset();
            $this->logger->log("Starting generator\n");

            $efactory = $this->factory->getInstanceFor('EngineFactory');

            $failed = array_diff($config->getRequiredEngines(), $efactory->getEngineList());
            if (count($failed)) {
               $list = join("', '", $failed);
               throw new ApplicationException("The engine(s) '$list' is/are not registered", ApplicationException::UnknownEngine);
            }

            $generator = $this->factory->getInstanceFor('Generator');

            foreach($config->getActiveBuilds() as $buildCfg) {
                $generator->addEngine( $efactory->getInstanceFor($buildCfg) );
            }
            $pconfig = $config->getProjectConfig();

            $generator->run($this->factory->getInstanceFor('Container', $pconfig->getWorkDirectory()), $pconfig->isPublicOnlyMode());
            $this->logger->log("Generator process completed");
        }

    }

    class ApplicationException extends \Exception {
        const UnknownEngine = 1;
    }
}