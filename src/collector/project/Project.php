<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Collector;

use TheSeer\fDOM\fDOMDocument;
use TheSeer\fDOM\fDOMElement;
use TheSeer\fDOM\fDOMException;
use TheSeer\phpDox\FileInfo;

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
    private $source;

    /**
     * @var IndexCollection
     */
    private $index;

    /**
     * @var SourceFile[]
     */
    private $files = [];

    private $saveUnits = [];

    private $loadedUnits = [];

    /**
     * @param $srcDir
     * @param $xmlDir
     */
    public function __construct(FileInfo $srcDir, FileInfo $xmlDir) {
        $this->xmlDir = $xmlDir;
        $this->srcDir = $srcDir;
        $this->initCollections();
    }

    public function getSourceDir(): FileInfo {
        return $this->srcDir;
    }

    public function getXmlDir(): FileInfo {
        return $this->xmlDir;
    }

    /**
     * @param FileInfo $file
     */
    public function addFile(SourceFile $file): bool {
        $isNew = $this->source->addFile($file);

        if ($isNew) {
            $this->removeFileReferences($file->getPathname());
            $this->files[$file->getPathname()] = $file;
        }

        return $isNew;
    }

    public function removeFile(FileInfo $file): void {
        $this->removeFileReferences($file->getPathname());
        unset($this->files[$file->getPathname()]);
        $this->source->removeFile($file);
    }

    public function addClass(ClassObject $class): void {
        $this->loadedUnits[$class->getName()] = $class;
        $this->registerForSaving($class);
        $this->index->addClass($class);
    }

    public function addInterface(InterfaceObject $interface): void {
        $this->loadedUnits[$interface->getName()] = $interface;
        $this->registerForSaving($interface);
        $this->index->addInterface($interface);
    }

    public function addTrait(TraitObject $trait): void {
        $this->loadedUnits[$trait->getName()] = $trait;
        $this->registerForSaving($trait);
        $this->index->addTrait($trait);
    }

    public function getIndex(): fDOMDocument {
        return $this->index->export();
    }

    public function getSourceTree(): fDOMDocument {
        return $this->source->export();
    }

    public function hasNamespaces(): bool {
        return $this->index->export()->query('count(//phpdox:namespace[not(@name="/")])') > 0;
    }

    public function getUnitByName(string $name) {
        if (isset($this->loadedUnits[$name])) {
            return $this->loadedUnits[$name];
        }

        $parts     = \explode('\\', $name);
        $local     = \array_pop($parts);
        $namespace = \implode('\\', $parts);
        $indexNode = $this->index->findUnitNodeByName($namespace, $local);

        if (!$indexNode) {
            throw new ProjectException("No unit with name '$name' found");
        }

        switch ($indexNode->localName) {
            case 'interface':
                {
                    $unit = new InterfaceObject();

                    break;
                }
            case 'trait':
                {
                    $unit = new TraitObject();

                    break;
                }
            case 'class':
                {
                    $unit = new ClassObject();

                    break;
                }
            default:
                {
                    throw new ProjectException(
                        \sprintf('Unexpected type "%s"', $indexNode->localName),
                        ProjectException::UnexpectedType
                    );
                }
        }

        $dom = new fDOMDocument();
        $dom->load($this->xmlDir . '/' . $indexNode->getAttribute('xml'));
        $unit->import($dom);

        return $unit;
    }

    public function cleanVanishedFiles(): array {
        $files = $this->source->getVanishedFiles();

        foreach ($files as $path) {
            $this->removeFileReferences($path);
        }

        return $files;
    }

    public function registerForSaving(AbstractUnitObject $unit): void {
        $this->saveUnits[$unit->getName()] = $unit;
    }

    public function save(): array {
        try {
            $map = $this->initDirectories();

            $indexDom    = $this->index->export();
            $reportUnits = $this->saveUnits;

            foreach ($this->saveUnits as $unit) {
                $reportUnits = $this->saveUnit($map, $reportUnits, $unit);
            }
            $indexDom->formatOutput       = true;
            $indexDom->preserveWhiteSpace = false;
            $indexDom->save($this->xmlDir . '/index.xml');

            $this->saveSources();

            $this->saveUnits = [];
            $this->files     = [];

            return $reportUnits;
        } catch (\Exception $e) {
            throw new ProjectException(
                \sprintf('An error occured while saving the collected data: %s', $e->getMessage()),
                ProjectException::ErrorWhileSaving,
                $e
            );
        }
    }

    /**
     * @param $fname
     */
    private function findAffectedUnits($fname): array {
        $affected = [];
        $dom      = new fDOMDocument();
        $dom->load($this->xmlDir . '/' . $fname);
        $dom->registerNamespace('phpdox', 'http://xml.phpdox.net/src');
        $extends = $dom->queryOne('//phpdox:extends');

        if ($extends instanceof fDOMElement) {
            try {
                $affected[$extends->getAttribute('full')] = $this->getUnitByName($extends->getAttribute('full'));
            } catch (ProjectException $e) {
            }
        }
        $implements = $dom->query('//phpdox:implements');

        foreach ($implements as $implement) {
            try {
                $affected[$implement->getAttribute('full')] = $this->getUnitByName($implement->getAttribute('full'));
            } catch (ProjectException $e) {
            }
        }

        return $affected;
    }

    private function saveUnit(array $map, array $reportUnits, AbstractUnitObject $unit) {
        $indexNode = $this->index->findUnitNodeByName($unit->getNamespace(), $unit->getLocalName());

        if (!$indexNode) {
            throw new ProjectException(
                \sprintf(
                    "Internal Error: Unit '%s' not found in index (ns: %s, n: %s).",
                    $unit->getName(),
                    $unit->getNamespace(),
                    $unit->getLocalName()
                ),
                ProjectException::UnitNotFoundInIndex
            );
        }
        $name                    = \str_replace('\\', '_', $unit->getName());
        $dom                     = $unit->export();
        $dom->formatOutput       = true;
        $dom->preserveWhiteSpace = false;
        $fname                   = $map[$dom->documentElement->localName] . '/' . $name . '.xml';

        try {
            $dom->save($this->xmlDir . '/' . $fname);
        } catch (fDOMException $e) {
            throw new ProjectException(
                \sprintf(
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
            $reportUnits = \array_merge($reportUnits, $this->findAffectedUnits($fname));
        } else {
            $indexNode->setAttribute('xml', $fname);
        }

        return $reportUnits;
    }

    private function initDirectories() {
        $map = ['class' => 'classes', 'trait' => 'traits', 'interface' => 'interfaces'];

        foreach ($map as $col) {
            $path = $this->xmlDir . '/' . $col;

            if (!\file_exists($path)) {
                \mkdir($path, 0777, true);
            }
        }

        return $map;
    }

    private function initCollections(): void {
        $this->source = new SourceCollection($this->srcDir);
        $srcFile      = $this->xmlDir . '/source.xml';

        if (\file_exists($srcFile)) {
            $dom = new fDOMDocument();
            $dom->load($srcFile);
            $this->source->import($dom);
        }

        $this->index = new IndexCollection($this->srcDir);
        $srcFile     = $this->xmlDir . '/index.xml';

        if (\file_exists($srcFile)) {
            $dom = new fDOMDocument();
            $dom->load($srcFile);
            $this->index->import($dom);
        }
    }

    /**
     * @param string $path
     */
    private function removeFileReferences($path): void {
        foreach ($this->index->findUnitNodesBySrcFile($path) as $node) {
            /** @var $node \DOMElement */
            $fname = $this->xmlDir . '/' . $node->getAttribute('xml');

            if (\file_exists($fname)) {
                \unlink($fname);
            }
            $node->parentNode->removeChild($node);
        }
    }

    private function saveSources(): void {
        foreach ($this->files as $file) {
            $tokenDom                     = $file->getTokens();
            $tokenDom->formatOutput       = true;
            $tokenDom->preserveWhiteSpace = false;
            $relName                      = 'tokens/' . $file->getRelative($this->srcDir, false) . '.xml';
            $fname                        = $this->xmlDir . '/' . $relName;
            $dir                          = \dirname($fname);

            if (!\file_exists($dir)) {
                \mkdir($dir, 0777, true);
            }

            try {
                $tokenDom->save($fname);
            } catch (fDOMException $e) {
                throw new ProjectException(
                    \sprintf(
                        "Internal Error: Token xml file '%s' could not be saved.",
                        $fname
                    ),
                    ProjectException::UnitCouldNotBeSaved,
                    $e
                );
            }
            $this->source->setTokenFileReference($file, $relName);
        }

        $sourceDom                     = $this->source->export();
        $sourceDom->formatOutput       = true;
        $sourceDom->preserveWhiteSpace = false;
        $sourceDom->save($this->xmlDir . '/source.xml');
    }
}
