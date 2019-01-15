<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Collector;

use TheSeer\fDOM\fDOMDocument;
use TheSeer\fDOM\fDOMElement;
use TheSeer\phpDox\DocBlock\DocBlock;
use TheSeer\phpDox\FileInfo;

abstract class AbstractUnitObject {
    /**
     * PHPDOX Namespace
     */
    public const XMLNS = 'http://xml.phpdox.net/src';

    /**
     * @var string
     */
    protected $rootName;

    /**
     * @var fDOMDocument
     */
    private $dom;

    /**
     * @var fDOMElement
     */
    private $rootNode;

    /**
     * @param string       $name
     * @param \SplFileInfo $file
     */
    public function __construct($name = null, SourceFile $file = null) {
        if ($this->rootName === null) {
            throw new UnitObjectException('No or invalid rootname set', UnitObjectException::InvalidRootname);
        }
        $this->dom = new fDOMDocument('1.0', 'UTF-8');
        $this->dom->registerNamespace('phpdox', self::XMLNS);
        $this->rootNode = $this->dom->createElementNS(self::XMLNS, $this->rootName);
        $this->dom->appendChild($this->rootNode);

        if ($name !== null) {
            $this->setName($name, $this->rootNode);
        }

        if ($file !== null) {
            $this->rootNode->appendChild($file->asNode($this->rootNode));
        }
        $this->setAbstract(false);
        $this->setFinal(false);
    }

    public function export(): fDOMDocument {
        return $this->dom;
    }

    public function import(fDOMDocument $dom): void {
        $this->dom      = $dom;
        $this->rootNode = $dom->documentElement;
        $this->dom->registerNamespace('phpdox', self::XMLNS);
    }

    public function getType() {
        return $this->rootNode->localName;
    }

    public function getLocalName(): string {
        return $this->rootNode->getAttribute('name');
    }

    public function getName(): string {
        return $this->rootNode->getAttribute('full');
    }

    public function getNamespace(): string {
        return $this->rootNode->getAttribute('namespace');
    }

    public function getSourceFilename(): ?FileInfo {
        $file = $this->rootNode->queryOne('phpdox:file');

        if (!$file) {
            return null;
        }

        return new FileInfo($file->getAttribute('path') . '/' . $file->getAttribute('file'));
    }

    public function getCompactDescription(): string {
        $desc = $this->rootNode->queryOne('phpdox:docblock/phpdox:description');

        if (!$desc || !$desc->hasAttribute('compact')) {
            return '';
        }

        return $desc->getAttribute('compact');
    }

    /**
     * @param int $endLine
     */
    public function setEndLine($endLine): void {
        $this->rootNode->setAttribute('end', $endLine);
    }

    /**
     * @param int $startLine
     */
    public function setStartLine($startLine): void {
        $this->rootNode->setAttribute('start', $startLine);
    }

    /**
     * @param bool $isAbstract
     */
    public function setAbstract($isAbstract): void {
        $this->rootNode->setAttribute('abstract', $isAbstract ? 'true' : 'false');
    }

    /**
     * @param bool $isFinal
     */
    public function setFinal($isFinal): void {
        $this->rootNode->setAttribute('final', $isFinal ? 'true' : 'false');
    }

    public function setDocBlock(DocBlock $docblock): void {
        $docNode = $docblock->asDom($this->dom);
        $this->rootNode->appendChild($docNode);
    }

    /**
     * @param $name
     */
    public function addExtends($name): void {
        $extends = $this->rootNode->appendElementNS(self::XMLNS, 'extends');
        $this->setName($name, $extends);
    }

    public function hasExtends(): bool {
        return $this->rootNode->queryOne('phpdox:extends') !== null;
    }

    /**
     * @throws UnitObjectException
     */
    public function getExtends() {
        if (!$this->hasExtends()) {
            throw new UnitObjectException('This unit does not extend any unit', UnitObjectException::NoExtends);
        }
        $result = [];

        foreach ($this->rootNode->query('phpdox:extends') as $ext) {
            $result[] = $ext->getAttribute('full');
        }

        return $result;
    }

    public function addExtender(self $unit): void {
        if ($this->rootNode->queryOne(\sprintf('phpdox:extenders/phpdox:*[@full = "%s"]', $unit->getName())) !== null) {
            return;
        }
        $extender = $this->addToContainer('extenders', 'extender');
        $this->setName($unit->getName(), $extender);
    }

    /**
     * @param $name
     */
    public function addImplements($name): void {
        $implements = $this->rootNode->appendElementNS(self::XMLNS, 'implements');
        $this->setName($name, $implements);
    }

    public function hasImplements(): bool {
        return $this->rootNode->query('phpdox:implements')->length > 0;
    }

    /**
     * @throws UnitObjectException
     */
    public function getImplements(): array {
        if (!$this->hasImplements()) {
            throw new UnitObjectException('This unit does not implement any interfaces', UnitObjectException::NoImplements);
        }
        $result = [];

        foreach ($this->rootNode->query('phpdox:implements') as $impl) {
            $result[] = $impl->getAttribute('full');
        }

        return $result;
    }

    public function usesTraits(): bool {
        return $this->rootNode->query('phpdox:uses')->length > 0;
    }

    /**
     * @param $name
     */
    public function usesTrait($name): bool {
        return $this->rootNode->query(\sprintf('phpdox:uses[@full="%s"]', $name))->length > 0;
    }

    /**
     * @param string $name
     */
    public function addTrait($name): TraitUseObject {
        $traituse = new TraitUseObject($this->rootNode->appendElementNS(self::XMLNS, 'uses'));
        $traituse->setName($name);

        return $traituse;
    }

    /**
     * @throws UnitObjectException
     */
    public function getUsedTraits(): array {
        if (!$this->usesTraits()) {
            throw new UnitObjectException('This unit does not use any traits', UnitObjectException::NoTraitsUsed);
        }
        $result = [];

        foreach ($this->rootNode->query('phpdox:uses') as $trait) {
            $result[] = $trait->getAttribute('full');
        }

        return $result;
    }

    /**
     * @param $name
     *
     * @throws UnitObjectException
     */
    public function getTraitUse($name): TraitUseObject {
        $node = $this->rootNode->queryOne(
            \sprintf('phpdox:uses[@full="%s"]', $name)
        );

        if (!$node) {
            throw new UnitObjectException(
                \sprintf('Trait "%s" not used', $name),
                UnitObjectException::NoSuchTrait
            );
        }

        return new TraitUseObject($node);
    }

    public function getAmbiguousTraitUse() {
        $node = $this->rootNode->queryOne('phpdox:ambiguous[@type="trait-alias"]');

        if (!$node) {
            $node = $this->rootNode->appendElementNS(self::XMLNS, 'ambiguous');
            $node->setAttribute('type', 'trait-alias');
        }

        return new TraitUseObject($node);
    }

    /**
     * @param string $dependency
     */
    public function markDependencyAsUnresolved($dependency): void {
        $depNode = $this->rootNode->queryOne(
            \sprintf('//phpdox:implements[@full="%1$s"]|//phpdox:extends[@full="%1$s"]|//phpdox:uses[@full="%1$s"]', $dependency)
        );

        if (!$depNode) {
            throw new UnitObjectException(
                \sprintf('No dependency "%s" found in unit %s', $dependency, $this->getName()),
                UnitObjectException::NoSuchDependency
            );
        }
        $depNode->setAttribute('unresolved', 'true');
    }

    public function addMethod($name) {
        switch ($name) {
            case '__construct':
                {
                    $nodeName = 'constructor';

                    break;
                }
            case '__destruct':
                {
                    $nodeName = 'destructor';

                    break;
                }
            default:
                $nodeName = 'method';
        }
        $method = new MethodObject($this, $this->rootNode->appendElementNS(self::XMLNS, $nodeName));
        $method->setName($name);

        return $method;
    }

    /**
     * @return MethodObject[]
     */
    public function getExportedMethods(): array {
        $result = [];
        $xpath  = '(phpdox:constructor|phpdox:destructor|phpdox:method)[@visibility="public" or @visibility="protected"]';

        foreach ($this->rootNode->query($xpath) as $node) {
            $result[] = new MethodObject($this, $node);
        }

        return $result;
    }

    /**
     * @param $name
     */
    public function addMember($name): MemberObject {
        $member = new MemberObject($this->rootNode->appendElementNS(self::XMLNS, 'member'));
        $member->setName($name);

        return $member;
    }

    public function getExportedMembers(): array {
        $result = [];
        $xpath  = 'phpdox:member[@visibility="public" or @visibility="protected"]';

        foreach ($this->rootNode->query($xpath) as $node) {
            $result[] = new MemberObject($node);
        }

        return $result;
    }

    /**
     * @param $name
     */
    public function addConstant($name): ConstantObject {
        $const = new ConstantObject($this->rootNode->appendElementNS(self::XMLNS, 'constant'));
        $const->setName($name);

        return $const;
    }

    public function getConstants(): array {
        $result = [];
        $xpath  = 'phpdox:constant';

        foreach ($this->rootNode->query($xpath) as $node) {
            $result[] = new ConstantObject($node);
        }

        return $result;
    }

    public function importExports(self $unit, $container = 'parent'): void {
        $parent = $this->rootNode->queryOne(\sprintf('//phpdox:%s[@full="%s"]', $container, $unit->getName()));

        if ($parent instanceof fDOMElement) {
            $parent->parentNode->removeChild($parent);
        }

        $parent = $this->rootNode->appendElementNS(self::XMLNS, $container);
        $parent->setAttribute('full', $unit->getName());
        $parent->setAttribute('namespace', $unit->getNamespace());
        $parent->setAttribute('name', $unit->getLocalName());

        if ($unit->hasExtends()) {
            foreach ($unit->getExtends() as $name) {
                $extends = $parent->appendElementNS(self::XMLNS, 'extends');
                $this->setName($name, $extends);
            }
        }

        if ($unit->hasImplements()) {
            foreach ($unit->getImplements() as $name) {
                $implements = $parent->appendElementNS(self::XMLNS, 'implements');
                $this->setName($name, $implements);
            }
        }

        if ($unit->usesTraits()) {
            foreach ($unit->getUsedTraits() as $name) {
                $uses = $parent->appendElementNS(self::XMLNS, 'uses');
                $this->setName($name, $uses);
            }
        }

        foreach ($unit->getConstants() as $constant) {
            $parent->appendChild($this->dom->importNode($constant->export(), true));
        }

        foreach ($unit->getExportedMembers() as $member) {
            $memberNode = $this->dom->importNode($member->export(), true);
            $this->adjustStaticResolution($memberNode);
            $parent->appendChild($memberNode);
        }

        foreach ($unit->getExportedMethods() as $method) {
            $methodNode = $this->dom->importNode($method->export(), true);
            $this->adjustStaticResolution($methodNode);
            $parent->appendChild($methodNode);

            if ($this->hasMethod($method->getName())) {
                $unitMethod = $this->getMethod($method->getName());

                if ($unitMethod->hasInheritDoc()) {
                    $unitMethod->inhertDocBlock($method);
                }
            }
        }
    }

    public function importTraitExports(self $trait, TraitUseObject $use): void {
        $container = $this->rootNode->queryOne(
            \sprintf(
                'phpdox:trait[@full="%s"]',
                $trait->getName()
            )
        );

        if ($container instanceof fDOMElement) {
            $container->parentNode->removeChild($container);
        }

        $container = $this->rootNode->appendElementNS(self::XMLNS, 'trait');
        $this->setName($trait->getName(), $container);

        if ($trait->hasExtends()) {
            foreach ($trait->getExtends() as $name) {
                $extends = $container->appendElementNS(self::XMLNS, 'extends');
                $this->setName($name, $extends);
            }
        }

        if ($trait->usesTraits()) {
            foreach ($trait->getUsedTraits() as $name) {
                $used = $container->appendElementNS(self::XMLNS, 'uses');
                $this->setName($name, $used);
            }
        }

        foreach ($trait->getConstants() as $constant) {
            $container->appendChild($this->dom->importNode($constant->export(), true));
        }

        foreach ($trait->getExportedMembers() as $member) {
            $memberNode = $this->dom->importNode($member->export(), true);
            $this->adjustStaticResolution($memberNode);
            $container->appendChild($memberNode);
        }

        $ambiguousContainer = $this->dom->queryOne('//phpdox:ambiguous[@type="trait-alias"]');

        foreach ($trait->getExportedMethods() as $method) {
            $methodName = $method->getName();
            $methodNode = $this->dom->importNode($method->export(), true);

            if (!$use->isExcluded($methodName)) {
                $container->appendChild($methodNode);
            }

            $this->adjustStaticResolution($methodNode);

            if ($ambiguousContainer !== null) {
                $ambiguousMethod = $ambiguousContainer->queryOne(
                    \sprintf('phpdox:alias[@method="%s"]', $methodName)
                );

                if ($ambiguousMethod !== null) {
                    $usesNode = $this->dom->queryOne(
                        \sprintf('//phpdox:uses[@full="%s"]', $trait->getName())
                    );
                    $usesNode->appendChild($ambiguousMethod);

                    if ($ambiguousContainer->query('phpdox:alias')->length === 0) {
                        $ambiguousContainer->parentNode->removeChild($ambiguousContainer);
                        $ambiguousContainer = null;
                    }
                }
            }

            $aliasNode = null;

            if ($use->isAliased($methodName)) {
                $aliasNode = $methodNode->cloneNode(true);
                $aliasNode->setAttribute('original', $aliasNode->getAttribute('name'));
                $aliasNode->setAttribute('name', $use->getAliasedName($methodName));

                if ($use->hasAliasedModifier($methodName)) {
                    $aliasNode->setAttribute('visibility', $use->getAliasedModifier($methodName));
                }
                $container->appendChild($aliasNode);
            }
        }
    }

    /**
     * @param $name
     */
    protected function setName($name, fDOMElement $ctx): void {
        $parts     = \explode('\\', $name);
        $local     = \array_pop($parts);
        $namespace = \implode('\\', $parts);
        $ctx->setAttribute('full', $name);
        $ctx->setAttribute('namespace', $namespace);
        $ctx->setAttribute('name', $local);
    }

    protected function getRootNode() {
        return $this->rootNode;
    }

    /**
     * @param $containerName
     * @param $elementName
     */
    protected function addToContainer($containerName, $elementName): fDOMElement {
        $container = $this->rootNode->queryOne('phpdox:' . $containerName);

        if (!$container) {
            $container = $this->rootNode->appendElementNS(self::XMLNS, $containerName);
        }

        return $container->appendElementNS(self::XMLNS, $elementName);
    }

    private function hasMethod($name) {
        return $this->dom->query(
                \sprintf('phpdox:method[@name="%s"]', $name)
            )->length > 0;
    }

    private function getMethod($name) {
        $ctx = $this->dom->queryOne(
            \sprintf('phpdox:method[@name="%s"]', $name)
        );

        if (!$ctx) {
            throw new UnitObjectException(
                \sprintf('Method "%s" not found', $name),
                UnitObjectException::NoSuchMethod
            );
        }

        return new MethodObject($this, $ctx);
    }

    private function adjustStaticResolution(fDOMElement $ctx): void {
        $container = $ctx->queryOne('.//phpdox:docblock/phpdox:return|.//phpdox:docblock/phpdox:var');

        if (!$container || $container->getAttribute('resolution') !== 'static') {
            return;
        }
        $type = $container->queryOne('phpdox:type');

        if (!$type) {
            return;
        }

        foreach (['full', 'namespace', 'name'] as $attribute) {
            $type->setAttribute($attribute, $this->rootNode->getAttribute($attribute));
        }
    }
}
