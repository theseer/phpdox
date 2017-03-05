<?php

namespace TheSeer\phpDox\Generator\Enricher {

    use TheSeer\fDOM\fDOMDocument;
    use TheSeer\fDOM\fDOMElement;
    use TheSeer\fDOM\fDOMException;
    use TheSeer\phpDox\Generator\AbstractUnitObject;
    use TheSeer\phpDox\Generator\ClassStartEvent;
    use TheSeer\phpDox\Generator\InterfaceStartEvent;
    use TheSeer\phpDox\Generator\TraitStartEvent;

    class PHPCs extends CheckStyle {

        const XMLNS = 'http://xml.phpdox.net/src';
        const FINDINGS_XPATH = '/phpcs/file';

        /**
         * @return string
         */
        public function getName() {
            return 'PHPCS XML';
        }

        protected function processFinding(fDOMDocument $dom, $ref, \DOMElement $finding) {
            $enrichment = $this->getEnrichtmentContainer($ref, 'checkstyle');
            $enrichFinding = $dom->createElementNS(self::XMLNS, $finding->tagName);
            $enrichment->appendChild($enrichFinding);
            foreach($finding->attributes as $attr) {
                if ($attr->localName == 'severity') {
                    continue;
                }
                $enrichFinding->setAttributeNode($dom->importNode($attr, true));
            }
            $enrichFinding->setAttribute('message', $finding->nodeValue);
        }
    }

}
