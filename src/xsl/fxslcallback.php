<?php
/**
 * Copyright (c) 2011-2014 Arne Blankerts <arne@blankerts.de>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 *   * Redistributions of source code must retain the above copyright notice,
 *     this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright notice,
 *     this list of conditions and the following disclaimer in the documentation
 *     and/or other materials provided with the distribution.
 *
 *   * Neither the name of Arne Blankerts nor the names of contributors
 *     may be used to endorse or promote products derived from this software
 *     without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT  * NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER ORCONTRIBUTORS
 * BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 * OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 *
 * @category  PHP
 * @package   TheSeer\fXSL
 * @author    Arne Blankerts <arne@blankerts.de>
 * @copyright Arne Blankerts <arne@blankerts.de>, All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://github.com/theseer/fxsl
 *
 */

namespace TheSeer\fXSL {

    /**
     * fXSLCallback
     *
     * This is a wrapper class around objects that are to be used for callbacks by fXSLTProcessor.
     *
     * It generates namespaced functions mapping the actual call to easy to use xpath functions, injecting
     * the required func and result nodes into the stylesheet
     *
     * @category  PHP
     * @package   TheSeer\fXSL
     * @author    Arne Blankerts <arne@blankerts.de>
     * @access    public
     */
    class fXSLCallback {

        /**
         * The namespace to be used for the callback functions
         *
         * @var string
         */
        private $xmlns;

        /**
         * The prefix used for mapping the xsl function calls
         *
         * @var string
         */
        private $prefix;

        /**
         * The registerd instance of the wrapped class
         *
         * @var object
         */
        private $object;

        /**
         * List of blacklisted method names
         *
         * @var array
         */
        private $blacklist = NULL;

        /**
         * List of whitelisted method names
         *
         * @var array
         */
        private $whitelist = NULL;

        /**
         * Constructor
         *
         * @param string $xmlns   The namespace to use
         * @param string $prefix  The prefix to map the namespace to
         */
        public function __construct($xmlns = '', $prefix = '') {
            $this->xmlns = $xmlns;
            $this->prefix = $prefix;
        }

        /**
         * Setter Method to define the Object to be wrapped
         *
         * @param $object
         *
         * @return void
         */
        public function setObject($object) {
            $this->object = $object;
        }

        /**
         * Getter to get the currently registered Object
         *
         * @return Object
         */
        public function getObject() {
            return $this->object;
        }

        /**
         * Setter method to define a whitelist of methods that can be called from xsl
         *
         * @param array $methods
         */
        public function setWhitelist(array $methods) {
            $this->whitelist = $methods;
        }

        /**
         * Setter method to define a blacklist of methods that can not be called from xsl
         *
         * @param array $methods
         */
        public function setBlacklist(array $methods) {
            $this->blacklist = $methods;
        }

        /**
         * Getter to get the namespace set at construction time
         *
         * @return string
         */
        public function getNamespace() {
            return $this->xmlns;
        }

        /**
         * Helper to inject the needed exslt function nodes in an XSL Stylesheet
         *
         * @param \DomDocument $stylesheet  Stylesheet to inject the wrapper code into
         * @param String       $key         Instance hash used for mapping in fXSLTProcessor
         *
         * @return void
         */
        public function injectCallbackCode(\DomDocument $stylesheet, $key) {
            $root = $stylesheet->documentElement;

            $this->setStylesheetProperties($root);

            $frag = $stylesheet->createDocumentFragment();
            $frag->appendXML("\n<!-- Injected by fXSLCallback - START -->\n");

            $rfo = new \ReflectionObject($this->object);
            $methods = $rfo->getMethods(\ReflectionMethod::IS_PUBLIC & ~\ReflectionMethod::IS_STATIC);
            if (!empty($methods)) {
                $this->registerMethods($frag, $key, $methods);
            }

            $frag->appendXML("\n<!-- Injected by fXSLCallback - END -->\n");
            $root->appendChild($frag);
        }

        /**
         * Internal helper to set the needed attributes on the xsl:stylesheet node
         *
         * @param \DOMElement $node
         */
        private function setStylesheetProperties(\DOMElement $node) {
            $node->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:func', 'http://exslt.org/functions');
            $node->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:php', 'http://php.net/xsl');
            $node->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:' . $this->prefix, $this->xmlns);

            $eep = explode(' ', $node->getAttribute('extension-element-prefixes'));
            $eep = array_unique(array_merge($eep, array('php', 'func')));
            $node->setAttribute('extension-element-prefixes', trim(join(' ', $eep)));

            $erp = explode(' ', $node->getAttribute('exclude-result-prefixes'));
            $erp = array_unique(array_merge($erp, array('php', 'func', $this->prefix)));
            $node->setAttribute('exclude-result-prefixes', trim(join(' ', $erp)));
        }

        /**
         * Internal helper to register a list of methods as xsl functions
         *
         * @param \DomDocumentFragment $ctx      A DomDocumentFragement to inject the method code into
         * @param string               $key      The hash of the class instance to refer to
         * @param Array                $methods  An array of ReflectionMethod object instances to register
         */
        private function registerMethods(\DomDocumentFragment $ctx, $key, Array $methods) {
            $xslPrefix = $ctx->ownerDocument->lookupPrefix('http://www.w3.org/1999/XSL/Transform');

            foreach ($methods as $m) {
                /** @var \ReflectionMethod $m */
                if ((!empty($this->blacklist) && in_array($m->getName(), $this->blacklist)) ||
                    (!empty($this->whitelist) && !in_array($m->getName(), $this->whitelist))
                ) {
                    continue;
                }

                $xml = sprintf(
                    '<func:function xmlns:func="http://exslt.org/functions" xmlns:%s="%s" name="%s:%s">',
                    $this->prefix,
                    $this->xmlns,
                    $this->prefix,
                    $m->getName()
                );

                $payload = array();
                foreach ($m->getParameters() as $p) {
                    // TODO: Check the type and bail out if it's not possible to provide it from xsl context
                    $xml .= sprintf('<%s:param xmlns:%s="http://www.w3.org/1999/XSL/Transform" name="%s" />', $xslPrefix, $xslPrefix, $p->getName());

                    // TODO: Add string() wrapper if needed
                    $payload[] = '$' . $p->getName();
                }

                $xml .= sprintf(
                    '<func:result xmlns:func="http://exslt.org/functions" select="php:function(\'%s\', string(\'%s\'), string(\'%s\'), string(\'%s\')%s)" />',
                    '\TheSeer\fXSL\fXSLTProcessor::callbackHook',
                    $key,
                    $this->xmlns,
                    $m->getName(),
                    count($payload) ? ', ' . join(', ', $payload) : ''
                );

                $xml .= '</func:function>';
                $ctx->appendXML($xml);
            }
        }
    }
}