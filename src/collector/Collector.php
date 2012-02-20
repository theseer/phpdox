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
 */
namespace TheSeer\phpDox {

    use \TheSeer\DirectoryScanner\PHPFilterIterator;
    use \TheSeer\fDOM\fDOMDocument;

    class Collector {

        /**
         * Starting index position in src path string to use in store
         * @var int
         */
        protected $srcIndex = 0;

        /**
         * Logger for progress and error reporting
         *
         * @var Logger
         */
        protected $logger;

        protected $factory;
        protected $xmlDir;
        protected $publicOnly;

        protected $parseErrors = array();

        public function __construct(ProgressLogger $logger, FactoryInterface $factory, $xmlDir, $publicOnly) {
            $this->logger = $logger;
            $this->xmlDir = $xmlDir;
            $this->factory = $factory;
            $this->publicOnly = $publicOnly;
        }

        /**
         * Setter to overwrite the default source directory string index position
         *
         * @param string $dir Directory to change source directory to
         */
        public function setStartIndex($index) {
            $this->srcIndex = $index;
        }


        public function hasParseErrors() {
            return count($this->parseErrors) > 0;
        }

        public function getParseErrors() {
            return $this->parseErrors;
        }

        /**
         * Main executer of the collector, looping over the iterator with found files
         *
         */
        public function run(\Iterator $dirIterator, Container $container) {
            $worker = new PHPFilterIterator($dirIterator);
            foreach($worker as $file) {
                try {
                    if ($container->needsUpdate($this->srcIndex, $file)) {
                        $this->processFile($file, $container);
                        $container->registerFile($this->srcIndex, $file);
                        $this->logger->progress('processed');
                    } else {
                        $this->logger->progress('cached');
                    }
                } catch (\pdepend\reflection\exceptions\ParserException $e) {
                    $this->parseErrors[] = $file;
                    $this->logger->progress('failed');
                } catch (\Exception $e) {
                    throw new CollectorException(
                        "Exception processing '" . $file->getPathname() . '."',
                        CollectorException::ProcessingError,
                        $e,
                        $file
                     );
                }
            }
            $this->logger->completed();
        }


        protected function processFile(\SPLFileInfo $file, Container $container) {
            $info = new \finfo();
            $encoding = $info->file($file, FILEINFO_MIME_ENCODING);

            $session = new \pdepend\reflection\ReflectionSession();
            $session->addClassFactory( new \pdepend\reflection\factories\NullReflectionClassFactory() );
            $query = $session->createFileQuery();
            $matches = $query->find( $file->getPathname() );
            $aliasMap = $query->getAliasMap();
            $classBuilder = $this->factory->getInstanceFor('ClassBuilder', $aliasMap, $this->publicOnly, $encoding);

            foreach ( $matches as $class ) {
                $dom = $this->getWorkDocument($file);
                $classBuilder->process($dom, $class);
                $fname = $this->saveWorkDocument($dom, $class);
                $container->registerUnit($dom, $fname);
            }
        }

        protected function getWorkDocument(\SPLFileInfo $file) {
            $dom = new fDOMDocument('1.0', 'UTF-8');
            $dom->preserveWhitespace = true;
            $dom->registerNamespace('phpdox', 'http://xml.phpdox.de/src#');
            $root = $dom->createElementNS('http://xml.phpdox.de/src#', 'file');
            $dom->appendChild($root);

            $head = $root->appendElementNS('http://xml.phpdox.de/src#', 'head');
            $head->setAttribute('path', $file->getPath());
            $head->setAttribute('file', $file->getFilename());
            $head->setAttribute('realpath', $file->getRealPath());
            $head->setAttribute('size', $file->getSize());
            $head->setAttribute('time', date('c', $file->getCTime()));
            $head->setAttribute('unixtime', $file->getCTime());
            $head->setAttribute('sha1', sha1_file($file->getPathname()));

            return $dom;
        }

        protected function saveWorkDocument(fDOMDocument $dom, $class) {
            $path = $this->xmlDir;
            $path .= $class->isInterface() ? '/interfaces' : '/classes';
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            $name = str_replace('\\','_', $dom->queryOne('//phpdox:class|//phpdox:interface|//phpdox:trait')->getAttribute('full'));

            $fname = $path . '/' . $name . '.xml';
            $dom->formatOutput = true;
            $dom->preserveWhiteSpace = true;
            $dom->save($fname);
            return $fname;
        }

    }

    class CollectorException extends HasFileInfoException {
        const ProcessingError = 1;
    }

}