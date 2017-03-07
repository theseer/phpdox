<?php

namespace TheSeer\phpDox\Generator\Enricher {

    use TheSeer\fDOM\fDOMDocument;

    class PHPCs extends CheckStyle {

        const XMLNS = 'http://xml.phpdox.net/src';
        const FINDINGS_XPATH = '/phpcs/file';

        /**
         * @return string
         */
        public function getName() {
            return 'PHPCS XML';
        }

        protected function processFinding(fDOMDocument $dom, $ref, \DOMElement $finding, $elementName = null) {
            $enrichFinding = parent::processFinding($dom, $ref, $finding, $finding->tagName);
            $enrichFinding->setAttribute('message', $finding->nodeValue);
            return $enrichFinding;
        }
    }

}
