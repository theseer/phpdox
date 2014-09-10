<?php
namespace TheSeer\phpDox\Generator\Enricher {

    use TheSeer\fDOM\fDOMDocument;
    use TheSeer\fDOM\fDOMException;
    use TheSeer\phpDox\Generator\PHPDoxStartEvent;

    class PHPLoc extends  AbstractEnricher implements StartEnricherInterface {

        /**
         * @var fDOMDocument
         */
        private $dom;

        public function __construct(PHPLocConfig $config) {
            $this->loadXML($config->getLogFilePath());
        }

        /**
         * @return string
         */
        public function getName() {
            return 'PHPLoc xml';
        }

        public function enrichStart(PHPDoxStartEvent $event) {
            $index = $event->getIndex()->asDom();
            $enrichment = $this->getEnrichtmentContainer($index->documentElement, 'phploc');

            // Import nodes in a loop to fix empty namespaces until sebastian fixes phploc to generate
            // "proper" xml ;)
            foreach($this->dom->documentElement->getElementsByTagName('*') as $node) {
                /** @var \DOMNode $node */

                $enrichment->appendChild(
                    $index->createElementNS(
                        'http://xml.phpdox.net/src',
                        $node->localName,
                        $node->nodeValue
                    )
                );

            }
        }

        private function loadXML($fname) {
            try {
                if (!file_exists($fname)) {
                    throw new EnricherException(
                        sprintf('PHPLoc xml file "%s" not found.', $fname),
                        EnricherException::LoadError
                    );
                }
                $this->dom = new fDOMDocument();
                $this->dom->load($fname);
            } catch (fDOMException $e) {
                throw new EnricherException(
                    'Parsing PHPLoc xml file failed: ' . $e->getMessage(),
                    EnricherException::LoadError
                );
            }
        }
    }

}
