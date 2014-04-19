<?php
    /**
     * Copyright (c) 2010-2014 Arne Blankerts <arne@blankerts.de>
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
namespace TheSeer\phpDox\Collector\Backend {

    use TheSeer\phpDox\Collector\AbstractVariableObject;
    use TheSeer\phpDox\Collector\InlineComment;
    use TheSeer\phpDox\Collector\MethodObject;
    use TheSeer\phpDox\DocBlock\Parser as DocBlockParser;
    use PhpParser\NodeVisitorAbstract;

    /**
     *
     */
    class UnitCollectingVisitor extends NodeVisitorAbstract {

        /**
         * @var \TheSeer\phpDox\DocBlock\Parser
         */
        private $dockblocParser;

        /**
         * @var array
         */
        private $aliasMap = array();

        /**
         * @var string
         */
        private $namespace = '\\';

        /**
         * @var ParseResult
         */
        private $result;

        /**
         * @var
         */
        private $unit;

        /**
         * @param \TheSeer\phpDox\DocBlock\Parser $parser
         * @param ParseResult                     $result
         */
        public function __construct(DocBlockParser $parser, ParseResult $result) {
            $this->dockblocParser = $parser;
            $this->result = $result;
        }

        /**
         * @param \PhpParser\Node $node
         */
        public function enterNode(\PhpParser\Node $node) {
            if ($node instanceof \PhpParser\Node\Stmt\Namespace_) {
                $this->namespace = join('\\', $node->name->parts);
                $this->aliasMap['::context'] = $this->namespace;
            } else if ($node instanceof \PhpParser\Node\Stmt\UseUse) {
                $this->aliasMap[$node->alias] = join('\\', $node->name->parts);
            } else if ($node instanceof \PhpParser\Node\Stmt\Class_) {
                $this->unit = $this->result->addClass((string)$node->namespacedName);
                $this->processUnit($node);
                return;
            } else if ($node instanceof \PhpParser\Node\Stmt\Interface_) {
                $this->unit = $this->result->addInterface((string)$node->namespacedName);
                $this->processUnit($node);
                return;
            } else if ($node instanceof \PhpParser\Node\Stmt\Trait_) {
                $this->unit = $this->result->addTrait((string)$node->namespacedName);
                $this->processUnit($node);
                return;
            } else if ($node instanceof \PhpParser\Node\Stmt\Property) {
                $this->processProperty($node);
                return;
            } else if ($node instanceof \PhpParser\Node\Stmt\ClassMethod) {
                $this->processMethod($node);
                return;
            } elseif ($node instanceof \PhpParser\Node\Stmt\ClassConst) {
                $this->processClassConstant($node);
            } elseif ($node instanceof \PhpParser\Comment) {
                //
            }
        }

        /**
         * @param \PhpParser\Node $node
         */
        public function leaveNode(\PhpParser\Node $node) {
            if ($node instanceof \PhpParser\Node\Stmt\Class_
                || $node instanceof \PhpParser\Node\Stmt\Interface_
                || $node instanceof \PhpParser\Node\Stmt\Trait_) {
                $this->unit = NULL;
                return;
            }
        }

        /**
         * @param $node
         */
        private function processUnit($node) {
            $this->unit->setStartLine($node->getAttribute('startLine'));
            $this->unit->setEndLine($node->getAttribute('endLine'));
            if ($node instanceof \PhpParser\Node\Stmt\Class_) {
                $this->unit->setAbstract($node->isAbstract());
                $this->unit->setFinal($node->isFinal());
            } else {
                $this->unit->setAbstract(FALSE);
                $this->unit->setFinal(FALSE);
            }

            $docComment = $node->getDocComment();
            if ($docComment !== NULL) {
                $block = $this->dockblocParser->parse($docComment, $this->aliasMap);
                $this->unit->setDocBlock($block);
            }

            /** @var \PhpParser\Node\Stmt\Class_ $node */
            if (count($node->extends) > 0) {
                if (is_array($node->extends)) {
                    foreach($node->extends as $extends) {
                        $this->unit->addExtends(join('\\', $extends->parts));
                    }
                } else {
                    $this->unit->addExtends(join('\\', $node->extends->parts));
                }

            }

            if (count($node->implements) > 0) {
                foreach($node->implements as $implements) {
                    $this->unit->addImplements(join('\\', $implements->parts));
                }
            }
        }

        /**
         * @param \PhpParser\Node\Stmt\ClassMethod $node
         */
        private function processMethod(\PhpParser\Node\Stmt\ClassMethod $node) {

            /** @var $method \TheSeer\phpDox\Collector\MethodObject */
            $method = $this->unit->addMethod($node->name);
            $method->setStartLine($node->getAttribute('startLine'));
            $method->setEndLine($node->getAttribute('endLine'));
            $method->setAbstract($node->isAbstract());
            $method->setFinal($node->isFinal());
            $method->setStatic($node->isStatic());

            $visibility = 'public';
            if ($node->isPrivate()) {
                $visibility = 'private';
            } elseif ($node->isProtected()) {
                $visibility = 'protected';
            }
            $method->setVisibility($visibility);
            $docComment = $node->getDocComment();
            if ($docComment !== NULL) {
                $block = $this->dockblocParser->parse($docComment, $this->aliasMap);
                $method->setDocBlock($block);
            }
            $this->processMethodParams($method, $node->params);
            if ($node->stmts) {
                $this->processInlineComments($method, $node->stmts);
            }
        }

        private function processInlineComments(MethodObject $method, array $stmts) {
            foreach($stmts as $stmt) {
                if ($stmt->hasAttribute('comments')) {
                    foreach($stmt->getAttribute('comments') as $comment) {
                        $inline = new InlineComment($comment->getLine(), $comment->getText());
                        if ($inline->getCount() != 0) {
                            $method->addInlineComment($inline);
                        }
                    }
                }
                if ($stmt->stmts) {
                    $this->processInlineComments($method, $stmt->stmts);
                }
            }
        }

        /**
         * @param MethodObject $method
         * @param array                                $params
         */
        private function processMethodParams(MethodObject $method, array $params) {
            foreach($params as $param) {
                /** @var $param \PhpParser\Node\Param  */
                $parameter = $method->addParameter($param->name);
                $parameter->setByReference($param->byRef);
                $this->setVariableType($parameter, $param->type);
                $this->setVariableDefaultValue($parameter, $param->default);
            }
            //die();
        }

        private function processClassConstant(\PhpParser\Node\Stmt\ClassConst $node) {
            $constNode = $node->consts[0];
            $const = $this->unit->addConstant($constNode->name);
            $const->setValue($constNode->getAttribute('originalValue'));
            $docComment = $node->getDocComment();
            if ($docComment !== NULL) {
                $block = $this->dockblocParser->parse($docComment, $this->aliasMap);
                $const->setDocBlock($block);
            }
        }

        private function processProperty(\PhpParser\Node\Stmt\Property $node) {
            $property = $node->props[0];
            $member = $this->unit->addMember($property->name);
            $this->setVariableType($member, $property->type);
            $this->setVariableDefaultValue($member, $property->default);
            $visibility = 'public';
            if ($node->isPrivate()) {
                $visibility = 'private';
            } elseif ($node->isProtected()) {
                $visibility = 'protected';
            }
            $member->setVisibility($visibility);
            $docComment = $node->getDocComment();
            if ($docComment !== NULL) {
                $block = $this->dockblocParser->parse($docComment, $this->aliasMap);
                $member->setDocBlock($block);
            }
            $member->setLine($node->getLine());
        }

        private function setVariableType(AbstractVariableObject $variable, $type = NULL) {
            if ($type === NULL) {
                $variable->setType('{unknown}');
                return;
            }
            if ($type === 'array') {
                $variable->setType('array');
                return;
            }
            if ($type instanceof \PhpParser\Node\Name\FullyQualified) {
                $variable->setType( (string)$type);
                return;
            }
            $type = (string)$type;
            if (isset($this->aliasMap[$type])) {
                $type = $this->aliasMap[$type];
            } elseif ($type[0]!='\\') {
                $type = $this->namespace . '\\' . $type;
            }
            $variable->setType($type);
        }

        /**
         * @param AbstractVariableObject     $variable
         * @param \PhpParser\Node\Expr       $default
         * @return string
         */
        private function setVariableDefaultValue(AbstractVariableObject $variable, \PhpParser\Node\Expr $default = NULL) {
            if ($default === NULL) {
                return;
            }
            if ($default instanceof \PhpParser\Node\Scalar\String) {
                $variable->setDefault($default->getAttribute('originalValue'));
                if ($variable->getType() == '{unknown}') {
                    $variable->setType('string');
                }
                return;
            }
            if ($default instanceof \PhpParser\Node\Scalar\LNumber ||
                $default instanceof \PhpParser\Node\Expr\UnaryMinus ||
                $default instanceof \PhpParser\Node\Expr\UnaryPlus) {
                $variable->setDefault($default->getAttribute('originalValue'));
                if ($variable->getType() == '{unknown}') {
                    $variable->setType('integer');
                }
                return;
            }
            if ($default instanceof \PhpParser\Node\Scalar\DNumber) {
                $variable->setDefault($default->getAttribute('originalValue'));
                if ($variable->getType() == '{unknown}') {
                    $variable->setType('float');
                }
                return;
            }
            if ($default instanceof \PhpParser\Node\Expr\Array_) {
                //var_dump($default);
                //$parameter->setDefault(join('\\', $default->items));
                if ($variable->getType() == '{unknown}') {
                    $variable->setType('array');
                }
                return;
            }
            if ($default instanceof \PhpParser\Node\Expr\ClassConstFetch) {
                $variable->setDefault(join('\\', $default->class->parts) . '::' . $default->name);
                return;
            }
            if ($default instanceof \PhpParser\Node\Expr\ConstFetch) {
                $variable->setDefault(join('\\', $default->name->parts));
                return;
            }
            if ($default instanceof \PhpParser\Node\Scalar\MagicConst\Trait_) {
                $variable->setName('__TRAIT__');
                return;
            }
            if ($default instanceof \PhpParser\Node\Scalar\MagicConst\Class_) {
                $variable->setDefault('__CLASS__');
                return;
            }
            if ($default instanceof \PhpParser\Node\Scalar\MagicConst\Method) {
                $variable->setName('__METHOD__');
                return;
            }
            if ($default instanceof \PhpParser\Node\Scalar\MagicConst\Dir) {
                $variable->setName('__DIR__');
                return;
            }
            if ($default instanceof \PhpParser\Node\Scalar\MagicConst\File) {
                $variable->setName('__FILE__');
                return;
            }
            if ($default instanceof \PhpParser\Node\Scalar\MagicConst\Function_) {
                $variable->setName('__FUNC__');
                return;
            }
            if ($default instanceof \PhpParser\Node\Scalar\MagicConst\Line) {
                $variable->setName('__LINE__');
                return;
            }

            $type = get_class($default);
            $line = $default->startLine;
            $file = $this->result->getFileName();
            throw new ParseErrorException("Unexpected expression type '$type' for default value in line $line of file '$file'", ParseErrorException::UnexpectedExpr);
        }

    }
}
