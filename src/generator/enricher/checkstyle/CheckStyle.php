<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator\Enricher;

use TheSeer\fDOM\fDOMDocument;
use TheSeer\fDOM\fDOMElement;
use TheSeer\fDOM\fDOMException;
use TheSeer\phpDox\Generator\AbstractUnitObject;
use TheSeer\phpDox\Generator\ClassStartEvent;
use TheSeer\phpDox\Generator\InterfaceStartEvent;
use TheSeer\phpDox\Generator\TraitStartEvent;

class CheckStyle extends AbstractEnricher implements ClassEnricherInterface, TraitEnricherInterface, InterfaceEnricherInterface {
    public const FINDINGS_XPATH = '/checkstyle/file';

    public const XML_STYLE = 'checkstyle';

    protected $config;

    protected $findings;

    public function __construct(CheckStyleConfig $config) {
        $this->config = $config;
        $this->loadFindings($config->getLogFilePath());
    }

    public function getName(): string {
        return 'CheckStyle XML';
    }

    public function enrichClass(ClassStartEvent $event): void {
        $this->enrichUnit($event->getClass());
    }

    public function enrichInterface(InterfaceStartEvent $event): void {
        $this->enrichUnit($event->getInterface());
    }

    public function enrichTrait(TraitStartEvent $event): void {
        $this->enrichUnit($event->getTrait());
    }

    protected function processFinding(fDOMDocument $dom, $ref, \DOMElement $finding, $elementName = null) {
        $enrichment    = $this->getEnrichtmentContainer($ref, 'checkstyle');
        $enrichFinding = $dom->createElementNS(static::XMLNS, ($elementName ?: $finding->getAttribute('severity', 'error')));
        $enrichment->appendChild($enrichFinding);

        foreach ($finding->attributes as $attr) {
            if ($attr->localName == 'severity') {
                continue;
            }
            $enrichFinding->setAttributeNode($dom->importNode($attr, true));
        }

        return $enrichFinding;
    }

    private function enrichUnit(AbstractUnitObject $ctx): void {
        $file = $ctx->getSourceFile();

        if (isset($this->findings[$file])) {
            $this->processFindings($ctx->asDom(), $this->findings[$file]);
        }
    }

    private function loadFindings($xmlFile): void {
        $this->findings = [];

        try {
            if (!\file_exists($xmlFile)) {
                throw new EnricherException(
                    \sprintf('Logfile "%s" not found.', $xmlFile),
                    EnricherException::LoadError
                );
            }
            $dom = new fDOMDocument();
            $dom->load($xmlFile);

            foreach ($dom->query(static::FINDINGS_XPATH) as $file) {
                $this->findings[$file->getAttribute('name')] = $file->query('*');
            }
        } catch (fDOMException $e) {
            throw new EnricherException(
                \sprintf('Parsing %s logfile failed: %s', static::XML_STYLE, $e->getMessage()),
                EnricherException::LoadError
            );
        }
    }

    private function processFindings(fDOMDocument $dom, \DOMNodeList $findings): void {
        foreach ($findings as $finding) {
            /** @var fDOMElement $finding */
            $line = $finding->getAttribute('line');
            $ref  = $dom->queryOne(\sprintf('//phpdox:*/*[@line = %d or (@start <= %d and @end >= %d)]', $line, $line, $line));

            if (!$ref) {
                // One src file may contain multiple classes/traits/interfaces, so the
                // finding might not apply to the current object since findings are based on filenames
                // but we have individual objects - so we just ignore the finding for this context
                continue;
            }
            $this->processFinding($dom, $ref, $finding);
        }
    }
}
