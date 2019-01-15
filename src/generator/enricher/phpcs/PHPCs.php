<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator\Enricher;

use TheSeer\fDOM\fDOMDocument;

class PHPCs extends CheckStyle {
    public const XMLNS = 'http://xml.phpdox.net/src';

    public const FINDINGS_XPATH = '/phpcs/file';

    public function getName(): string {
        return 'PHPCS XML';
    }

    protected function processFinding(fDOMDocument $dom, $ref, \DOMElement $finding, $elementName = null) {
        $enrichFinding = parent::processFinding($dom, $ref, $finding, $finding->tagName);
        $enrichFinding->setAttribute('message', $finding->nodeValue);

        return $enrichFinding;
    }
}
