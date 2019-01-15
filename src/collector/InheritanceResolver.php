<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Collector;

use TheSeer\fDOM\fDOMDocument;
use TheSeer\phpDox\InheritanceConfig;
use TheSeer\phpDox\ProgressLogger;

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

    private $dependencyStack = [];

    /**
     * @var array
     */
    private $unresolved = [];

    /**
     * @var array
     */
    private $errors = [];

    public function __construct(ProgressLogger $logger) {
        $this->logger = $logger;
    }

    /**
     * @throws ProjectException
     * @throws UnitObjectException
     */
    public function resolve(array $changed, Project $project, InheritanceConfig $config): void {
        if (\count($changed) == 0) {
            return;
        }
        $this->logger->reset();
        $this->logger->log("Resolving inheritance\n");

        $this->project = $project;
        $this->config  = $config;

        $this->setupDependencies();

        foreach ($changed as $unit) {
            /** @var AbstractUnitObject $unit */
            if ($unit->hasExtends()) {
                foreach ($unit->getExtends() as $name) {
                    try {
                        $extendedUnit = $this->getUnitByName($name);
                        $this->processExtends($unit, $extendedUnit);
                    } catch (ProjectException $e) {
                        $this->addUnresolved($unit, $name);
                    }
                }
            }

            if ($unit->hasImplements()) {
                foreach ($unit->getImplements() as $implements) {
                    try {
                        $implementsUnit = $this->getUnitByName($implements);
                        $this->processImplements($unit, $implementsUnit);
                    } catch (ProjectException $e) {
                        $this->addUnresolved($unit, $implements);
                    }
                }
            }

            if ($unit->usesTraits()) {
                foreach ($unit->getUsedTraits() as $traitName) {
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
                foreach ($this->unresolved[$unitName] as $missingUnit) {
                    $unit->markDependencyAsUnresolved($missingUnit);
                }
            }

            $this->logger->progress('processed');
        }

        $this->project->save();
        $this->logger->completed();
    }

    public function hasUnresolved() {
        return \count($this->unresolved) > 0;
    }

    public function getUnresolved() {
        return $this->unresolved;
    }

    public function hasErrors() {
        return \count($this->errors) > 0;
    }

    public function getErrors() {
        return $this->errors;
    }

    private function addError(AbstractUnitObject $unit, $errorInfo): void {
        $unitName = $unit->getName();

        if (!isset($this->errors[$unitName])) {
            $this->errors[$unitName] = [];
        }
        $this->errors[$unitName][] = $errorInfo;
    }

    private function addUnresolved(AbstractUnitObject $unit, $missingUnit): void {
        $unitName = $unit->getName();

        if (!isset($this->unresolved[$unitName])) {
            $this->unresolved[$unitName] = [];
        }
        $this->unresolved[$unitName][] = $missingUnit;
        $this->project->registerForSaving($unit);
    }

    private function processExtends(AbstractUnitObject $unit, AbstractUnitObject $extends): void {
        $this->project->registerForSaving($unit);
        $this->project->registerForSaving($extends);

        $extends->addExtender($unit);
        $unit->importExports($extends, 'parent');

        if ($extends->hasExtends()) {
            foreach ($extends->getExtends() as $name) {
                try {
                    $extendedUnit = $this->getUnitByName($name);
                    $this->processExtends($unit, $extendedUnit, $extendedUnit);
                } catch (ProjectException $e) {
                    $this->addUnresolved($unit, $name);
                }
            }
        }

        if ($extends->hasImplements()) {
            foreach ($extends->getImplements() as $implements) {
                try {
                    $implementsUnit = $this->getUnitByName($implements);
                    $this->processImplements($unit, $implementsUnit, $implementsUnit);
                } catch (ProjectException $e) {
                    $this->addUnresolved($unit, $implements);
                }
            }
        }

        if ($extends->usesTraits()) {
            foreach ($extends->getUsedTraits() as $traitName) {
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

    private function processImplements(AbstractUnitObject $unit, AbstractUnitObject $implements): void {
        $this->project->registerForSaving($unit);
        $this->project->registerForSaving($implements);

        if (!$implements instanceof InterfaceObject) {
            $this->addError(
                $unit,
                \sprintf(
                    'Trying to implement "%s" which is a %s',
                    $implements->getName(),
                    $implements->getType()
                )
            );

            return;
        }
        $implements->addImplementor($unit);
        $unit->importExports($implements, 'interface');

        if ($implements->hasImplements()) {
            foreach ($implements->getImplements() as $implementing) {
                try {
                    $implementsUnit = $this->getUnitByName($implementing);
                    $this->processExtends($unit, $implementsUnit, $implementsUnit);
                } catch (ProjectException $e) {
                    $this->addUnresolved($unit, $implementing);
                }
            }
        }
    }

    private function processTraitUse(AbstractUnitObject $unit, TraitUseObject $use, AbstractUnitObject $trait): void {
        $this->project->registerForSaving($unit);
        $this->project->registerForSaving($trait);

        $trait->addUser($unit);
        $unit->importTraitExports($trait, $use);

        if ($trait->hasExtends()) {
            foreach ($trait->getExtends() as $name) {
                try {
                    $extendedUnit = $this->getUnitByName($name);
                    $this->processExtends($unit, $extendedUnit, $extendedUnit);
                } catch (ProjectException $e) {
                    $this->addUnresolved($unit, $name);
                }
            }
        }

        if ($trait->usesTraits()) {
            foreach ($trait->getUsedTraits() as $traitName) {
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

    private function setupDependencies(): void {
        $this->dependencyStack = [
            $this->project,
        ];

        $publicOnlyMode = $this->config->isPublicOnlyMode();

        foreach ($this->config->getDependencyDirectories() as $depDir) {
            $idxName = $depDir . '/index.xml';

            if (!\file_exists($idxName)) {
                $this->logger->log("'$idxName' not found - skipping dependency");

                continue;
            }
            $dom = new fDOMDocument();
            $dom->load($idxName);
            $this->dependencyStack[] = new Dependency($dom, $this->project, $publicOnlyMode);
        }
    }

    /**
     * @param $name
     *
     * @throws ProjectException
     */
    private function getUnitByName($name): AbstractUnitObject {
        foreach ($this->dependencyStack as $dependency) {
            try {
                return $dependency->getUnitByName($name);
            } catch (\Exception $e) {
            }
        }

        throw new ProjectException("No unit with name '$name' found");
    }
}
