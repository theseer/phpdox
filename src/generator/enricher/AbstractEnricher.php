<?php
namespace TheSeer\phpDox\Generator\Enricher {

    use TheSeer\fDOM\fDOMElement;

    class AbstractEnricher {

        const XMLNS = 'http://xml.phpdox.net/src';

        /**
         * @param fDOMElement $node
         * @param             $type
         *
         * @return fDOMElement
         */
        protected function getEnrichtmentContainer(fDOMElement $node, $type) {
            $dom = $node->ownerDocument;
            $container = $node->queryOne('phpdox:enrichments');
            if(!$container) {
                $container = $dom->createElementNS(self::XMLNS, 'enrichments');
                $node->appendChild($container);
            }

            $enrichment = $container->queryOne(
                $dom->prepareQuery('phpdox:enrichment[@type=:type]', array('type' => $type))
            );

            if (!$enrichment) {
                $enrichment = $dom->createElementNS(self::XMLNS, 'enrichment');
                $enrichment->setAttribute('type', $type);
                $container->appendChild($enrichment);
            }

            return $enrichment;
        }
    }

}
