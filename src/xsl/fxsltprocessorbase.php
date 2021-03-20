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
     * fXSLTProcessorBase
     *
     * This class extends the original XSLTProcessor with custom functionality
     * to allow nicer php level callbacks and use exceptions in favor of
     * semi-complete documents and standard php errors
     *
     * Note: This code switches to libxml internal errors mode
     *
     * @category  PHP
     * @package   TheSeer\fXSL
     * @author    Arne Blankerts <arne@blankerts.de>
     * @access    public
     */
    abstract class fXSLTProcessorBase extends \XSLTProcessor {

        /**
         * Static registry for registered callback objects
         *
         * @var array
         */
        private static $registry = array();

        /**
         * Flag to signal if initStyleSheet has been called
         *
         * @var boolean
         */
        protected $initDone = FALSE;

        /**
         * Flag to signal if registerPHPFunctions has been called
         *
         * @var boolean
         */
        private $registered = FALSE;

        /**
         * The given XSL Stylesheet to process
         *
         * @var \DOMDocument
         */
        private $stylesheet;

        /**
         * The spl_object_hash of the current instance
         *
         * @var string
         */
        private $hash;

        /**
         * Constructor, allowing to directly inject a Stylesheet for later processing
         *
         * @param \DomDocument $stylesheet A DomDocument containing an xslt stylesheet
         */
        public function __construct(\DomDocument $stylesheet = NULL) {
            $this->hash = spl_object_hash($this);
            libxml_use_internal_errors(TRUE);
            libxml_clear_errors();
            if ($stylesheet !== NULL) {
                $this->importStylesheet($stylesheet);
            }
            if (method_exists($this, 'setSecurityPrefs')) {
                $this->setSecurityPrefs(XSL_SECPREF_NONE);
            }
        }

        /**
         * Destructor to cleanup registry
         */
        public function __destruct() {
            unset(self::$registry[$this->hash]);
        }

        /**
         * Load a Stylesheet from a file and import it
         *
         * @param string $filename  Filename to load as stylesheet
         */
        public function loadStylesheetFromFile($filename) {
            $dom = new \DOMDocument();
            $dom->load($filename);
            if (count(libxml_get_errors()) != 0) {
                throw new fXSLTProcessorException('Error loading stylesheet', fXSLTProcessorException::ImportFailed);
            }
            $this->importStylesheet($dom);
        }

        /**
         * Load a Stylesheet from xml string and import it
         *
         * @param string $xml XML string of stylesheet
         */
        public function loadStylesheetFromXML($xml) {
            $dom = new \DOMDocument();
            $dom->loadXML($xml);
            if (count(libxml_get_errors()) != 0) {
                throw new fXSLTProcessorException('Error loading stylesheet', fXSLTProcessorException::ImportFailed);
            }
            $this->importStylesheet($dom);
        }

        /**
         * @see XSLTProcessor::importStylesheet()
         *
         * Extended version to throw exception on error
         */
        public function importStylesheet($stylesheet) {
            if ($stylesheet->documentElement->namespaceURI != 'http://www.w3.org/1999/XSL/Transform') {
                throw new fXSLTProcessorException(
                    "Namespace mismatch: Expected 'http://www.w3.org/1999/XSL/Transform' but '{$stylesheet->documentElement->namespaceURI}' found.",
                    fXSLTProcessorException::WrongNamespace
                );
            }
            $this->stylesheet = $stylesheet;
        }

        /**
         * @see XSLTProcessor::registerPHPFunctions()
         *
         * Extended version to enforce callability of fXSLProcessor::callbackHook and generally callable methods
         */
        public function registerPHPFunctions($restrict = NULL) {
            if (is_string($restrict)) {
                $restrict = array($restrict);
            }
            if (is_array($restrict)) {
                foreach ($restrict as $func) {
                    if (!is_callable($func)) {
                        throw new fXSLTProcessorException("'$func' is not a callable method or function", fXSLTProcessorException::NotCallable);
                    }
                }
                $restrict[] = '\TheSeer\fXSL\fXSLTProcessor::callbackHook';
            }
            $restrict === NULL ? parent::registerPHPFunctions() : parent::registerPHPFunctions($restrict);
            $this->registered = TRUE;
        }

        /**
         * @see XSLTProcessor::transformToDoc()
         *      Extended version to throw exception on error
         */
        protected function internalTransformToDoc($node) {
            if (!$this->initDone) {
                $this->initStylesheet();
            }
            libxml_clear_errors();
            $rc = parent::transformToDoc($node);
            if (count(libxml_get_errors()) != 0) {
                throw new fXSLTProcessorException('Error in transformation', fXSLTProcessorException::TransformationFailed);
            }
            return $rc;
        }

        /**
         * @see XSLTProcessor::transformToUri()
         *      Extended version to throw exception on error
         *
         */
        protected function internalTransformToUri($doc, $uri) {
            return $this->transformToDoc($doc)->save($uri);
        }

        /**
         * @see XSLTProcessor::transformToXml()
         *      Extended version to throw exception on error
         *
         */
        protected function internalTransformToXml($doc) {
            if (!$this->initDone) {
                $this->initStylesheet();
            }
            // Do not remap this to $this->transformToDoc(..)->saveXML()
            // for that will break xsl:output as text, as well as omit xml decl
            libxml_clear_errors();
            $rc = parent::transformToXml($doc);
            if (count(libxml_get_errors()) != 0) {
                throw new fXSLTProcessorException('Error in transformation', fXSLTProcessorException::TransformationFailed);
            }
            return $rc;
        }

        /**
         * Register an fXSLCallback object instance
         *
         * @param fXSLCallback $callback The instance of the fXSLCallback to register
         */
        public function registerCallback(fXSLCallback $callback) {
            $this->initDone = FALSE;
            if (!$this->registered) {
                $this->registerPHPFunctions();
            }

            if (!isset(self::$registry[$this->hash])) {
                self::$registry[$this->hash] = array();
            }
            self::$registry[$this->hash][$callback->getNamespace()] = $callback;
        }

        /**
         * Static method to be called from within xsl
         *
         * Additional parameters are going to get passed on the to method called
         *
         * @param string $hash       The spl_object_hash of the fXSLProcessor instance the call has been triggered in
         * @param string $namespace  The namespace of the class instance the call is ment for
         * @param string $method     The method to call on the instance specified by namespace
         *
         * @return string|\DomNode
         */
        public static function callbackHook($hash, $namespace, $method) {
            $obj = self::$registry[$hash][$namespace]->getObject();
            $params = array_slice(func_get_args(), 3);
            return call_user_func_array(array($obj, $method), $params);
        }

        /**
         * Internal helper to do the template initialisation and injection of registered objects
         */
        protected function initStylesheet() {
            $this->initDone = TRUE;
            libxml_clear_errors();

            if (isset(self::$registry[$this->hash])) {
                foreach (self::$registry[$this->hash] as $cb) {
                    $cb->injectCallbackCode($this->stylesheet, $this->hash);
                }
            }
            if (libxml_get_last_error()) {
                throw new fXSLTProcessorException('Error registering callbacks', fXSLTProcessorException::ImportFailed);
            }
            parent::importStylesheet($this->stylesheet);
            if (libxml_get_last_error()) {
                throw new fXSLTProcessorException('Error while importing given stylesheet', fXSLTProcessorException::ImportFailed);
            }
        }
    }

    /**
     * fXSLTProcessorException
     *
     * @category  PHP
     * @package   TheSeer\fXSL
     * @author    Arne Blankerts <arne@blankerts.de>
     * @access    public
     */
    class fXSLTProcessorException extends \Exception {

        const WrongNamespace = 1;
        const ImportFailed = 2;
        const NotCallable = 3;
        const UnknownInstance = 4;
        const TransformationFailed = 5;

        public function __construct($message, $code) {
            $errorList = libxml_get_errors();
            libxml_clear_errors();

            $message .= "\n\n";
            foreach ($errorList as $error) {
                // hack, skip "attempt to load external pseudo error"
                if ($error->code == '1543') {
                    continue;
                }

                if (empty($error->file)) {
                    $message .= '[XML-STRING] ';
                } else {
                    $message .= '[' . $error->file . '] ';
                }

                $message .= '[Line: ' . $error->line . ' - Column: ' . $error->column . '] ';

                switch ($error->level) {
                    case LIBXML_ERR_WARNING:
                        $message .= "Warning $error->code: ";
                        break;
                    case LIBXML_ERR_ERROR:
                        $message .= "Error $error->code: ";
                        break;
                    case LIBXML_ERR_FATAL:
                        $message .= "Fatal Error $error->code: ";
                        break;
                }

                $message .= str_replace("\n", '', $error->message) . "\n";
            }
            parent::__construct($message, $code);
        }

    }

}
