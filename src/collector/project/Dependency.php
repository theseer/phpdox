<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Collector;

use TheSeer\fDOM\fDOMDocument;

class Dependency {
    /**
     * @var fDOMDocument
     */
    private $index;

    /**
     * @var Project
     */
    private $project;

    /**
     * @var string
     */
    private $baseDir;

    /**
     * @var bool
     */
    private $publicOnlyMode;

    public function __construct(fDOMDocument $dom, Project $project, $publicOnlyMode) {
        $this->index   = $dom;
        $this->baseDir = \dirname(\str_replace('file:/', '', \urldecode($dom->documentURI)));
        $this->index->registerNamespace('phpdox', 'http://xml.phpdox.net/src');
        $this->project        = $project;
        $this->publicOnlyMode = $publicOnlyMode;
    }

    public function getUnitByName($name) {
        $parts     = \explode('\\', $name);
        $local     = \array_pop($parts);
        $namespace = \implode('\\', $parts);
        $indexNode = $this->index->queryOne(
            \sprintf('//phpdox:namespace[@name="%s"]/*[@name="%s"]', $namespace, $local)
        );

        if (!$indexNode) {
            throw new DependencyException(
                \sprintf("Unit '%s' not found", $name),
                DependencyException::UnitNotFound
            );
        }

        $dom = new fDOMDocument();
        $dom->load($this->baseDir . '/' . $indexNode->getAttribute('xml'));

        if ($this->publicOnlyMode) {
            foreach ($dom->query('//*[@visibility and not(@visibility = "public")]') as $node) {
                $node->parentNode->removeChild($node);
            }
        }

        switch ($indexNode->localName) {
            case 'interface':
                {
                    $unit = new InterfaceObject();
                    $unit->import($dom);
                    $this->project->addInterface($unit);

                    break;
                }
            case 'trait':
                {
                    $unit = new TraitObject();
                    $unit->import($dom);
                    $this->project->addTrait($unit);

                    break;
                }
            case 'class':
                {
                    $unit = new ClassObject();
                    $unit->import($dom);
                    $this->project->addClass($unit);

                    break;
                }
            default:
                {
                    throw new DependencyException(
                        \sprintf("Invalid unit type '%s'", $indexNode->localName),
                        DependencyException::InvalidUnitType
                    );
                }
        }

        return $unit;
    }
}
