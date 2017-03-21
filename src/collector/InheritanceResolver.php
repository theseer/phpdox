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
    use TheSeer\phpDox\ProgressLogger;
    use TheSeer\phpDox\InheritanceConfig;

    /**
     * Inheritance resolving class
     */
    class InheritanceResolver {

        /**
         * @var ProgressLogger
         */
        private $logger;

        /**
         * @var \TheSeer\phpDox\Collector\Project
         */
        private $project;

        /**
         * @var InheritanceConfig
         */
        private $config;

        private $dependencyStack = array();

        /**
         * @var array
         */
        private $unresolved = array();

        /**
         * @param ProgressLogger $logger
         */
        public function __construct(ProgressLogger $logger) {
            $this->logger = $logger;
        }

        /**
         * @param array             $changed
         * @param Project           $project
         * @param InheritanceConfig $config
         *
         * @throws ProjectException
         * @throws UnitObjectException
         */
        public function resolve(Array $changed, Project $project, InheritanceConfig $config) {
            if (count($changed) == 0) {
                return;
            }
            $this->logger->reset();
            $this->logger->log("Resolving inheritance\n");

            $this->project = $project;
            $this->config = $config;

            $this->setupDependencies();

            foreach($changed as $unit) {
                /** @var AbstractUnitObject $unit */
                if ($unit->hasExtends()) {
                    foreach($unit->getExtends() as $name) {
                        try {
                            $extendedUnit = $this->getUnitByName($name);
                            $this->processExtends($unit, $extendedUnit);
                        } catch (ProjectException $e) {
                            $this->addUnresolved($unit, $name);
                        }
                    }
                }
                if ($unit->hasImplements()) {
                    foreach($unit->getImplements() as $implements) {
                        try {
                            $implementsUnit = $this->getUnitByName($implements);
                            $this->processImplements($unit, $implementsUnit);
                        } catch (ProjectException $e) {
                            $this->addUnresolved($unit, $implements);
                        }
                    }
                }
                if ($unit->usesTraits()) {
                    foreach($unit->getUsedTraits() as $traitName) {
                        try {
                            $traitUnit = $this->getUnitByName($traitName);
                            $this->processTraitUse(
                                $unit,
                                $unit->getTraitUse($traitName),
                                $traitUnit
                            );
                        } catch (ProjectException $e) {
                            $this->addUnresolved($unit, $traitName);
                        }
                    }
                }

                $unitName = $unit->getName();
                if (isset($this->unresolved[$unitName])) {
                    foreach($this->unresolved[$unitName] as $missingUnit) {
                        $unit->markDependencyAsUnresolved($missingUnit);
                    }
                }

                $this->logger->progress('processed');
            }

            $this->project->save();
            $this->logger->completed();
        }

        public function hasUnresolved() {
            return count($this->unresolved) > 0;
        }

        public function getUnresolved() {
            return $this->unresolved;
        }

        private function addUnresolved(AbstractUnitObject $unit, $missingUnit) {
            $unitName = $unit->getName();
            if (!isset($this->unresolved[$unitName])) {
                $this->unresolved[$unitName] = array();
            }
            $this->unresolved[$unitName][] = $missingUnit;
            $this->project->registerForSaving($unit);
        }

        private function processExtends(AbstractUnitObject $unit, AbstractUnitObject $extends) {
            $this->project->registerForSaving($unit);
            $this->project->registerForSaving($extends);

            $extends->addExtender($unit);
            $unit->importExports($extends, 'parent');

            if ($extends->hasExtends()) {
                foreach($extends->getExtends() as $name) {
                    try {
                        $extendedUnit = $this->getUnitByName($name);
                        $this->processExtends($unit, $extendedUnit, $extendedUnit);
                    } catch (ProjectException $e) {
                        $this->addUnresolved($unit, $name);
                    }
                }
            }

            if ($extends->hasImplements()) {
                foreach($extends->getImplements() as $implements) {
                    try {
                        $implementsUnit = $this->getUnitByName($implements);
                        $this->processImplements($unit, $implementsUnit, $implementsUnit);
                    } catch (ProjectException $e) {
                        $this->addUnresolved($unit, $implements);
                    }
                }
            }

            if ($extends->usesTraits()) {
                foreach($extends->getUsedTraits() as $traitName) {
                    try {
                        $traitUnit = $this->getUnitByName($traitName);
                        $this->processTraitUse(
                            $unit,
                            $extends->getTraitUse($traitName),
                            $traitUnit
                        );
                    } catch (ProjectException $e) {
                        $this->addUnresolved($unit, $traitName);
                    }
                }
            }

        }

        private function processImplements(AbstractUnitObject $unit, AbstractUnitObject $implements) {
            $this->project->registerForSaving($unit);
            $this->project->registerForSaving($implements);

            $implements->addImplementor($unit);
            $unit->importExports($implements, 'interface');

            if ($implements->hasImplements()) {
                foreach($implements->getImplements() as $implementing) {
                    try {
                        $implementsUnit = $this->getUnitByName($implementing);
                        $this->processExtends($unit, $implementsUnit, $implementsUnit);
                    } catch (ProjectException $e) {
                        $this->addUnresolved($unit, $implementing);
                    }
                }
            }
        }

        private function processTraitUse(AbstractUnitObject $unit, TraitUseObject $use, AbstractUnitObject $trait) {
            $this->project->registerForSaving($unit);
            $this->project->registerForSaving($trait);

            $trait->addUser($unit);
            $unit->importTraitExports($trait, $use);

            if ($trait->hasExtends()) {
                foreach($trait->getExtends() as $name) {
                    try {
                        $extendedUnit = $this->getUnitByName($name);
                        $this->processExtends($unit, $extendedUnit, $extendedUnit);
                    } catch (ProjectException $e) {
                        $this->addUnresolved($unit, $name);
                    }
                }
            }

            if ($trait->usesTraits()) {
                foreach($trait->getUsedTraits() as $traitName) {
                    try {
                        $traitUnit = $this->getUnitByName($traitName);
                        $this->processTraitUse(
                            $unit,
                            $trait->getTraitUse($traitName),
                            $traitUnit
                        );
                    } catch (ProjectException $e) {
                        $this->addUnresolved($unit, $traitName);
                    }
                }
            }

        }

        private function setupDependencies() {
            $this->dependencyStack = array(
                $this->project,
            );
            foreach($this->config->getDependencyDirectories() as $depDir) {
                $idxName = $depDir . '/index.xml';
                if (!file_exists($idxName)) {
                    $this->logger->log("'$idxName' not found - skipping dependency");
                    continue;
                }
                $dom = new fDOMDocument();
                $dom->load($idxName);
                $this->dependencyStack[] = new Dependency($dom, $this->project);
            }
        }

        /**
         * @param $name
         *
         * @return AbstractUnitObject
         * @throws ProjectException
         */
        private function getUnitByName($name) {
            foreach($this->dependencyStack as $dependency) {
                try {
                    return $dependency->getUnitByName($name);
                } catch (\Exception $e) {}
            }
            throw new ProjectException("No unit with name '$name' found");
        }

    }

}
