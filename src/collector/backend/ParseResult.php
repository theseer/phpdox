<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Collector\Backend;

use TheSeer\phpDox\Collector\ClassObject;
use TheSeer\phpDox\Collector\InterfaceObject;
use TheSeer\phpDox\Collector\TraitObject;

class ParseResult {
    /**
     * @var \SplFileInfo
     */
    private $file;

    /**
     * @var ClassObject[]
     */
    private $classes = [];

    /**
     * @var InterfaceObject[]
     */
    private $interfaces = [];

    /**
     * @var TraitObject[]
     */
    private $traits = [];

    public function __construct(\SplFileInfo $file) {
        $this->file = $file;
    }

    public function getFileName() {
        return $this->file->getRealPath();
    }

    /**
     * @param $name
     */
    public function addClass($name): ClassObject {
        $obj                  = new ClassObject($name, $this->file);
        $this->classes[$name] = $obj;

        return $obj;
    }

    /**
     * @param $name
     */
    public function addInterface($name): InterfaceObject {
        $obj                     = new InterfaceObject($name, $this->file);
        $this->interfaces[$name] = $obj;

        return $obj;
    }

    /**
     * @param $name
     */
    public function addTrait($name): TraitObject {
        $obj                 = new TraitObject($name, $this->file);
        $this->traits[$name] = $obj;

        return $obj;
    }

    public function hasClasses(): bool {
        return \count($this->classes) > 0;
    }

    public function hasInterfaces(): bool {
        return \count($this->interfaces) > 0;
    }

    public function hasTraits(): bool {
        return \count($this->traits) > 0;
    }

    /**
     * @return ClassObject[]
     */
    public function getClasses(): array {
        return $this->classes;
    }

    /**
     * @return InterfaceObject[]
     */
    public function getInterfaces(): array {
        return $this->interfaces;
    }

    /**
     * @return TraitObject[]
     */
    public function getTraits(): array {
        return $this->traits;
    }
}
