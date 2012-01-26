<?php
/**
 * Copyright (c) 2010 Arne Blankerts <arne@blankerts.de>
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
 * @package    phpDox
 * @author     Arne Blankerts <arne@blankerts.de>
 * @copyright  Arne Blankerts <arne@blankerts.de>, All rights reserved.
 * @license    BSD License
 */

namespace TheSeer\phpDox {

    use \TheSeer\fDOM\fDOMDocument;
    use \TheSeer\fDOM\fDOMElement;
    use \TheSeer\phpDox\DocBlock\Parser;

    class ClassBuilder {

        protected $ctx;
        protected $publicOnly;
        protected $encoding;
        protected $parser;
        protected $aliasMap;

        public function __construct(Parser $parser, fDOMElement $ctx, array $aliasMap, $publicOnly = false, $encoding = 'ISO-8859-1') {
            $this->parser = $parser;
            $this->ctx = $ctx;
            $this->aliasMap = $aliasMap;
            $this->publicOnly = $publicOnly;
            $this->encoding = $encoding;
        }

        public function process(\ReflectionClass $class) {

            $node = $this->ctx->appendElementNS('http://xml.phpdox.de/src#', $class->isInterface() ? 'interface' : 'class' );

            $node->setAttribute('full', $class->getName());
            $node->setAttribute('name', $class->getShortName());
            $node->setAttribute('final', $class->isFinal() ? 'true' : 'false');
            if ($node->nodeName === 'class') {
                $node->setAttribute('abstract', $class->isAbstract() ? 'true' : 'false');
            }

            $node->setAttribute('start', $class->getStartLine());
            $node->setAttribute('end', $class->getEndLine());

            if ($docComment = $class->getDocComment()) {
                $node->appendChild(
                    $this->processDocBlock($this->ctx->ownerDocument, $docComment)
                );
            }

            if ($extends = $class->getParentClass()) {
                $this->addReferenceNode($extends, $node, 'extends');
            }

            $implements = $class->getInterfaces();
            if (count($implements)>0) {
                foreach($implements as $i) {
                    $this->addReferenceNode($i, $node, 'implements');
                }
            }
            $this->processConstants($node, $class->getConstants());
            $this->processMembers($node, $class->getProperties());
            $this->processMethods($node, $class->getMethods());

            return $node;

        }

        protected function addReferenceNode(\ReflectionClass $class, fDOMElement $context, $nodeName) {
            $node = $context->appendElementNS('http://xml.phpdox.de/src#', $nodeName);
            $node->setAttribute($nodeName == 'extends' ? 'class' : 'interface', $class->getShortName());
            if ($class->inNamespace()) {
                $node->setAttribute('namespace', $class->getNamespaceName());
            }
            $node->setAttribute('full', $class->getName());
            return $node;
        }

        protected function addModifiers(fDOMElement $ctx, $src) {
            $ctx->setAttribute('static', $src->isStatic() ? 'true' : 'false');
            if ($src->isPrivate()) {
                $ctx->setAttribute('visibility', 'private');
            } else if ($src->isProtected()) {
                $ctx->setAttribute('visibility', 'protected');
            } else {
                $ctx->setAttribute('visibility', 'public');
            }
        }

        protected function processDocBlock(fDOMDocument $doc, $comment) {
            try {
                if (strtolower($this->encoding) != 'utf-8') {
                    $comment = iconv($this->encoding, 'UTF-8//TRANSLIT', $comment);
                }
                $docblock = $this->parser->parse($comment, $this->aliasMap);
                return $docblock->asDom($doc);
            } catch (\Exception $e) {
                throw new ClassBuilderException('Processing DocBlock failed', ClassBuilderException::DocBlockFailed, $e);
            }
        }

        protected function processConstants(fDOMElement $ctx, Array $constants) {
            foreach($constants as $constant => $value) {
                $constNode = $ctx->appendElementNS('http://xml.phpdox.de/src#', 'constant');
                $constNode->setAttribute('name', $constant);
                $constNode->setAttribute('value', $value);
            }
        }

        protected function processMembers(fDOMElement $ctx, Array $members) {
            foreach($members as $member) {
                if ($this->publicOnly && ($member->isPrivate() || $member->isProtected())) {
                    continue;
                }
                $memberNode = $ctx->appendElementNS('http://xml.phpdox.de/src#', 'member');
                $memberNode->setAttribute('name', $member->getName());
                $this->addModifiers($memberNode, $member);
                $this->processValue($memberNode, $member->getValue());
                if ($docComment = $member->getDocComment()) {
                    $memberNode->appendChild(
                    $this->processDocBlock($ctx->ownerDocument, $docComment)
                    );
                }
            }
        }

        protected function processMethods(fDOMElement $ctx, Array $methods) {
            foreach($methods as $method) {
                if ($this->publicOnly && ($method->isPrivate() || $method->isProtected())) {
                    continue;
                }

                if ($method->isConstructor()) {
                    $nodeName = 'constructor';
                } elseif ($method->isDestructor()) {
                    $nodeName = 'destructor';
                } else {
                    $nodeName = 'method';
                }
                $methodNode = $ctx->appendElementNS('http://xml.phpdox.de/src#', $nodeName);
                $methodNode->setAttribute('name', $method->getName());
                $methodNode->setAttribute('start', $method->getStartLine());
                $methodNode->setAttribute('end', $method->getEndLine());
                $methodNode->setAttribute('abstract', $method->isAbstract() ? 'true' : 'false');
                $methodNode->setAttribute('final', $method->isFinal() ? 'true' : 'false');

                $this->addModifiers($methodNode, $method);

                $docBlock = null;
                if ($docComment = $method->getDocComment()) {
                    $docBlock = $this->processDocBlock($ctx->ownerDocument, $docComment);
                    $methodNode->appendChild($docBlock);
                }
                $this->processParameters($methodNode, $method->getParameters(), $docBlock);

            }
        }

        protected function processParameters(fDOMElement $ctx, Array $parameters, fDOMElement $docBlock = null) {
            foreach($parameters as $idx => $param) {
                $paramNode = $ctx->appendElementNS('http://xml.phpdox.de/src#', 'parameter');
                $paramNode->setAttribute('name', '$'.$param->getName());
                if ($class = $param->getClass()) {
                    $paramNode->setAttribute('type', 'object');
                    $paramNode->setAttribute('class', $class->getShortName());
                    if ($class->inNamespace()) {
                        $paramNode->setAttribute('namespace', $class->getNamespaceName());
                    }
                } elseif ($param->isArray()) {
                    $paramNode->setAttribute('type', 'array');
                } else {
                    $paramNode->setAttribute('type', '{unknown}');
                }
                $paramNode->setAttribute('optional', $param->isOptional() ? 'true' : 'false');
                $paramNode->setAttribute('byreference', $param->isPassedByReference() ? 'true' : 'false');
                if ($param->isDefaultValueAvailable()) {
                    $this->processValue($paramNode, $param->getDefaultValue());
                }
            }
        }

        protected function processValue(fDOMElement $ctx, $src) {
            $value =  is_null($src) ? 'null' : var_export($src, true);

            // Temporary workaround due to an issue in pdepend staticReflection package.
            if (strpos($value, '\'__StaticReflectionConstantValue(') !== false) {
                $value = preg_replace("#'__StaticReflectionConstantValue\((.*)\)'#", '$1', $value);
            }

            $default = $ctx->appendElementNS('http://xml.phpdox.de/src#', 'default');
            $default->appendChild($ctx->ownerDocument->createTextnode($value));
        }
    }

    class ClassBuilderException extends \Exception {
        const DocBlockFailed = 1;
    }
}
