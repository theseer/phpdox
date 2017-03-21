<?php
/**
 * Copyright (c) 2010-2017 Arne Blankerts <arne@blankerts.de>
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
namespace TheSeer\phpDox\Collector {

    use TheSeer\fDOM\fDOMDocument;
    use TheSeer\fDOM\fDOMElement;
    use TheSeer\fDOM\fDOMException;
    use TheSeer\phpDox\FileInfo;

    /**
     *
     */
    class Project {

        /**
         * @var FileInfo
         */
        private $xmlDir;

        /**
         * @var FileInfo
         */
        private $srcDir;

        /**
         * @var SourceCollection
         */
        private $source = NULL;

        /**
         * @var IndexCollection
         */
        private $index = NULL;

        /**
         * @var SourceFile[]
         */
        private $files  = array();

        private $saveUnits = array();
        private $loadedUnits = array();

        /**
         * @param $srcDir
         * @param $xmlDir
         */
        public function __construct(FileInfo $srcDir, FileInfo $xmlDir) {
            $this->xmlDir = $xmlDir;
            $this->srcDir = $srcDir;
            $this->initCollections();
        }

        /**
         * @return FileInfo
         */
        public function getSourceDir() {
            return $this->srcDir;
        }

        /**
         * @return FileInfo
         */
        public function getXmlDir() {
            return $this->xmlDir;
        }

        /**
         * @param FileInfo $file
         * @return bool
         */
        public function addFile(SourceFile $file) {
            $isNew = $this->source->addFile($file);
            if ($isNew) {
                $this->removeFileReferences($file->getPathname());
                $this->files[$file->getPathname()] = $file;
            }
            return $isNew;
        }

        /**
         * @param FileInfo $file
         */
        public function removeFile(FileInfo $file) {
            $this->removeFileReferences($file->getPathname());
            unset($this->files[$file->getPathname()]);
            $this->source->removeFile($file);
        }

        /**
         * @param ClassObject $class
         */
        public function addClass(ClassObject $class) {
            $this->loadedUnits[$class->getName()] = $class;
            $this->registerForSaving($class);
            $this->index->addClass($class);
        }

        /**
         *
         */
        public function addInterface(InterfaceObject $interface) {
            $this->loadedUnits[$interface->getName()] = $interface;
            $this->registerForSaving($interface);
            $this->index->addInterface($interface);
        }

        /**
         *
         */
        public function addTrait(TraitObject $trait) {
            $this->loadedUnits[$trait->getName()] = $trait;
            $this->registerForSaving($trait);
            $this->index->addTrait($trait);
        }

        /**
         * @return fDOMDocument
         */
        public function getIndex() {
            return $this->index->export();
        }

        /**
         * @return fDOMDocument
         */
        public function getSourceTree() {
            return $this->source->export();
        }

        /**
         * @return bool
         */
        public function hasNamespaces() {
            return $this->index->export()->query('count(//phpdox:namespace[not(@name="/")])') > 0;
        }

        /**
         * @param $namespace
         * @param $name
         * @return fDOMElement
         */
        public function getUnitByName($name) {
            if (isset($this->loadedUnits[$name])) {
                return $this->loadedUnits[$name];
            }

            $parts = explode('\\', $name);
            $local = array_pop($parts);
            $namespace = join('\\', $parts);
            $indexNode = $this->index->findUnitNodeByName($namespace, $local);
            if (!$indexNode) {
                throw new ProjectException("No unit with name '$name' found");
            }

            switch ($indexNode->localName) {
                case 'interface': {
                    $unit = new InterfaceObject();
                    break;
                }
                case 'trait': {
                    $unit = new TraitObject();
                    break;
                }
                case 'class': {
                    $unit = new ClassObject();
                    break;
                }
                default: {
                    throw new ProjectException(
                        sprintf('Unexpected type "%s"', $indexNode->localName),
                        ProjectException::UnexpectedType
                    );
                }
            }

            $dom = new fDOMDocument();
            $dom->load($this->xmlDir . '/' . $indexNode->getAttribute('xml'));
            $unit->import($dom);

            return $unit;
        }

        /**
         * @return array
         */
        public function cleanVanishedFiles() {
            $files = $this->source->getVanishedFiles();
            foreach ($files as $path) {
                $this->removeFileReferences($path);
            }
            return $files;
        }


        public function registerForSaving(AbstractUnitObject $unit) {
            $this->saveUnits[$unit->getName()] = $unit;
        }

        /**
         * @return array
         */
        public function save() {
            try {
                $map = $this->initDirectories();

                $indexDom = $this->index->export();
                $reportUnits = $this->saveUnits;
                foreach($this->saveUnits as $unit) {
                    $reportUnits = $this->saveUnit($map, $reportUnits, $unit);
                }
                $indexDom->formatOutput = TRUE;
                $indexDom->preserveWhiteSpace = FALSE;
                $indexDom->save($this->xmlDir . '/index.xml');

                $this->saveSources();

                $this->saveUnits = array();
                $this->files = array();

                return $reportUnits;
            } catch (\Exception $e) {
                throw new ProjectException(
                    sprintf('An error occured while saving the collected data: %s', $e->getMessage()),
                    ProjectException::ErrorWhileSaving,
                    $e
                );
            }
        }

        /**
         * @param $fname
         *
         * @return array
         */
        private function findAffectedUnits($fname) {
            $affected = array();
            $dom = new fDOMDocument();
            $dom->load($this->xmlDir . '/' . $fname);
            $dom->registerNamespace('phpdox', 'http://xml.phpdox.net/src');
            $extends = $dom->queryOne('//phpdox:extends');
            if ($extends instanceof fDOMElement) {
                try {
                    $affected[$extends->getAttribute('full')] = $this->getUnitByName($extends->getAttribute('full'));
                } catch (ProjectException $e) {}
            }
            $implements = $dom->query('//phpdox:implements');
            foreach($implements as $implement) {
                try {
                    $affected[$implement->getAttribute('full')] = $this->getUnitByName($implement->getAttribute('full'));
                } catch (ProjectException $e) {}
            }
            return $affected;
        }

        private function saveUnit(array $map, array $reportUnits, AbstractUnitObject $unit) {
            $indexNode = $this->index->findUnitNodeByName($unit->getNamespace(), $unit->getLocalName());
            if (!$indexNode) {
                throw new ProjectException(
                    sprintf(
                        "Internal Error: Unit '%s' not found in index (ns: %s, n: %s).",
                        $unit->getName(),
                        $unit->getNamespace(),
                        $unit->getLocalName()
                    ),
                    ProjectException::UnitNotFoundInIndex
                );
            }
            $name = str_replace('\\', '_', $unit->getName());
            $dom = $unit->export();
            $dom->formatOutput = TRUE;
            $dom->preserveWhiteSpace = FALSE;
            $fname = $map[$dom->documentElement->localName] . '/' . $name . '.xml';
            try {
                $dom->save($this->xmlDir . '/' . $fname);
            } catch (fDOMException $e) {
                throw new ProjectException(
                    sprintf(
                        "Internal Error: Unit '%s' could not be saved (ns: %s, n: %s).",
                        $unit->getName(),
                        $unit->getNamespace(),
                        $unit->getLocalName()
                    ),
                    ProjectException::UnitCouldNotBeSaved,
                    $e
                );
            }
            if ($indexNode->hasAttribute('xml')) {
                $reportUnits = array_merge($reportUnits, $this->findAffectedUnits($fname));
            } else {
                $indexNode->setAttribute('xml', $fname);
            }

            return $reportUnits;
        }

        private function initDirectories() {
            $map = array('class' => 'classes', 'trait' => 'traits', 'interface' => 'interfaces');
            foreach ($map as $col) {
                $path = $this->xmlDir . '/' . $col;
                if (!file_exists($path)) {
                    mkdir($path, 0777, TRUE);
                }
            }
            return $map;
        }
        /**
         * @return void
         */
        private function initCollections() {
            $this->source = new SourceCollection($this->srcDir);
            $srcFile = $this->xmlDir . '/source.xml';
            if (file_exists($srcFile)) {
                $dom = new fDOMDocument();
                $dom->load($srcFile);
                $this->source->import($dom);
            }

            $this->index = new IndexCollection($this->srcDir);
            $srcFile = $this->xmlDir . '/index.xml';
            if (file_exists($srcFile)) {
                $dom = new fDOMDocument();
                $dom->load($srcFile);
                $this->index->import($dom);
            }

        }

        /**
         * @param string $path
         */
        private function removeFileReferences($path) {
            foreach($this->index->findUnitNodesBySrcFile($path) as $node) {
                /** @var $node \DOMElement */
                $fname = $this->xmlDir . '/' . $node->getAttribute('xml');
                if (file_exists($fname)) {
                    unlink($fname);
                }
                $node->parentNode->removeChild($node);
            }
        }

        private function saveSources() {
            foreach($this->files as $file) {
                $tokenDom = $file->getTokens();
                $tokenDom->formatOutput = TRUE;
                $tokenDom->preserveWhiteSpace = FALSE;
                $relName = 'tokens/' . $file->getRelative($this->srcDir, FALSE) . '.xml';
                $fname = $this->xmlDir . '/' . $relName;
                $dir = dirname($fname);
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                try {
                    $tokenDom->save($fname);
                } catch (fDOMException $e) {
                    throw new ProjectException(
                        sprintf(
                            "Internal Error: Token xml file '%s' could not be saved.",
                            $fname
                        ),
                        ProjectException::UnitCouldNotBeSaved,
                        $e
                    );
                }
                $this->source->setTokenFileReference($file, $relName);
            }

            $sourceDom = $this->source->export();
            $sourceDom->formatOutput = TRUE;
            $sourceDom->preserveWhiteSpace = FALSE;
            $sourceDom->save($this->xmlDir . '/source.xml');
        }

    }

}
