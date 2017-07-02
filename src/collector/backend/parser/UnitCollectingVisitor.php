<?php
    /**
     * Copyright (c) 2010-2017 Arne Blankerts <arne@blankerts.de>
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

    use TheSeer\phpDox\Collector\AbstractUnitObject;
    use TheSeer\phpDox\Collector\AbstractVariableObject;
    use TheSeer\phpDox\Collector\InlineComment;
    use TheSeer\phpDox\Collector\MethodObject;
    use TheSeer\phpDox\DocBlock\Parser as DocBlockParser;
    use PhpParser\NodeVisitorAbstract;
    use PhpParser\Node\Stmt as NodeType;

    /**
     *
     */
    class UnitCollectingVisitor extends NodeVisitorAbstract {

        /**
         * @var \TheSeer\phpDox\DocBlock\Parser
         */
        private $docBlockParser;

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
         * @var AbstractUnitObject
         */
        private $unit;

        private $modifier = array(
            NodeType\Class_::MODIFIER_PUBLIC    => 'public',
            NodeType\Class_::MODIFIER_PROTECTED => 'protected',
            NodeType\Class_::MODIFIER_PRIVATE   => 'private',
        );

        /**
         * @param \TheSeer\phpDox\DocBlock\Parser $parser
         * @param ParseResult                     $result
         */
        public function __construct(DocBlockParser $parser, ParseResult $result) {
            $this->docBlockParser = $parser;
            $this->result = $result;
        }

        /**
         * @param \PhpParser\Node $node
         *
         * @return int|null|\PhpParser\Node|void
         */
        public function enterNode(\PhpParser\Node $node) {
            if ($node instanceof NodeType\Namespace_ && $node->name != NULL) {
                $this->namespace = implode('\\', $node->name->parts);
                $this->aliasMap['::context'] = $this->namespace;
            } else if ($node instanceof NodeType\UseUse) {
                $this->aliasMap[$node->alias] = implode('\\', $node->name->parts);
            } else if ($node instanceof NodeType\Class_) {
                $this->aliasMap['::unit'] = (string)$node->namespacedName;
                $this->unit = $this->result->addClass((string)$node->namespacedName);
                $this->processUnit($node);
                return;
            } else if ($node instanceof NodeType\Interface_) {
                $this->aliasMap['::unit'] = (string)$node->namespacedName;
                $this->unit = $this->result->addInterface((string)$node->namespacedName);
                $this->processUnit($node);
                return;
            } else if ($node instanceof NodeType\Trait_) {
                $this->aliasMap['::unit'] = (string)$node->namespacedName;
                $this->unit = $this->result->addTrait((string)$node->namespacedName);
                $this->processUnit($node);
                return;
            } else if ($node instanceof NodeType\Property) {
                $this->processProperty($node);
                return;
            } else if ($node instanceof NodeType\ClassMethod) {
                $this->processMethod($node);
                return;
            } elseif ($node instanceof NodeType\ClassConst) {
                $this->processClassConstant($node);
            } elseif ($node instanceof NodeType\TraitUse) {
                $this->processTraitUse($node);
            }
        }

        /**
         * @param \PhpParser\Node $node
         *
         * @return false|int|null|\PhpParser\Node|\PhpParser\Node[]|void
         */
        public function leaveNode(\PhpParser\Node $node) {
            if ($node instanceof NodeType\Class_
                || $node instanceof NodeType\Interface_
                || $node instanceof NodeType\Trait_) {
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
            if ($node instanceof NodeType\Class_) {
                $this->unit->setAbstract($node->isAbstract());
                $this->unit->setFinal($node->isFinal());
            } else {
                $this->unit->setAbstract(FALSE);
                $this->unit->setFinal(FALSE);
            }

            $docComment = $node->getDocComment();
            if ($docComment !== NULL) {
                $block = $this->docBlockParser->parse($docComment, $this->aliasMap);
                $this->unit->setDocBlock($block);
            }

            if ($node->getType() != 'Stmt_Trait' && $node->extends !== NULL) {
                if (is_array($node->extends)) {
                    $extendsArray = $node->extends;
                    foreach ($extendsArray as $extends) {
                        $this->unit->addExtends(implode('\\', $extends->parts));
                    }
                } else {
                    $this->unit->addExtends(implode('\\', $node->extends->parts));
                }
            }

            if ($node->getType() === 'Stmt_Class') {
                foreach($node->implements as $implements) {
                    $this->unit->addImplements(implode('\\', $implements->parts));
                }
            }
        }

        private function processTraitUse(NodeType\TraitUse $node) {
            foreach($node->traits as $trait) {
                $traitUse = $this->unit->addTrait( (string)$trait );
                $traitUse->setStartLine($node->getAttribute('startLine'));
                $traitUse->setEndLine($node->getAttribute('endLine'));
            }

            foreach($node->adaptations as $adaptation) {
                if ($adaptation instanceof NodeType\TraitUseAdaptation\Alias) {
                    $traitUse = $this->getTraitUse((string)$adaptation->trait);
                    $traitUse->addAlias(
                        $adaptation->method,
                        $adaptation->newName,
                        $adaptation->newModifier ? $this->modifier[$adaptation->newModifier] : NULL
                    );
                } elseif ($adaptation instanceof NodeType\TraitUseAdaptation\Precedence) {
                    $traitUse = $this->getTraitUse((string)$adaptation->insteadof[0]);
                    $traitUse->addExclude($adaptation->method);
                } else {
                    throw new ParseErrorException(
                        sprintf('Unexpected adaption type %s', get_class($adaptation)),
                        ParseErrorException::UnexpectedExpr
                    );
                }
            }

        }

        private function getTraitUse($traitName) {
            if (!$this->unit->usesTtrait($traitName)) {
                throw new ParseErrorException(
                    sprintf('Referenced trait "%s" not used', $traitName),
                    ParseErrorException::GeneralParseError
                );
            }
            return $this->unit->getTraitUse($traitName);
        }

        /**
         * @param NodeType\ClassMethod $node
         */
        private function processMethod(NodeType\ClassMethod $node) {

            /** @var $method \TheSeer\phpDox\Collector\MethodObject */
            $method = $this->unit->addMethod($node->name);
            $method->setStartLine($node->getAttribute('startLine'));
            $method->setEndLine($node->getAttribute('endLine'));
            $method->setAbstract($node->isAbstract());
            $method->setFinal($node->isFinal());
            $method->setStatic($node->isStatic());

            $this->processMethodReturnType($method, $node->getReturnType());

            $visibility = 'public';
            if ($node->isPrivate()) {
                $visibility = 'private';
            } elseif ($node->isProtected()) {
                $visibility = 'protected';
            }
            $method->setVisibility($visibility);

            $docComment = $node->getDocComment();
            if ($docComment !== NULL) {
                $block = $this->docBlockParser->parse($docComment, $this->aliasMap);
                $method->setDocBlock($block);
            }

            $this->processMethodParams($method, $node->params);

            if ($node->stmts) {
                $this->processInlineComments($method, $node->stmts);
            }
        }

        private function processMethodReturnType(MethodObject $method, $returnType) {
            if ($returnType === null) {
                return;
            }

            if (in_array($returnType, ['void','float','int','string','bool','callable','array'])) {
                $returnTypeObject = $method->setReturnType($returnType);
                $returnTypeObject->setNullable(false);
                return;
            }

            if ($returnType instanceof \PhpParser\Node\Name\FullyQualified) {
                $returnTypeObject = $method->setReturnType($returnType->toString());
                $returnTypeObject->setNullable(false);
                return;
            }

            if ($returnType instanceof \PhpParser\Node\NullableType) {
                $returnTypeObject = $method->setReturnType($returnType->type);
                $returnTypeObject->setNullable(true);
                return;
            }
            throw new ParseErrorException("Unexpected return type definition", ParseErrorException::UnexpectedExpr);
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
        }

        private function processClassConstant(NodeType\ClassConst $node) {
            $constNode = $node->consts[0];
            $const = $this->unit->addConstant($constNode->name);

            $resolved = $this->resolveExpressionValue($constNode->value);

            $const->setValue($resolved['value']);
            if (isset($resolved['constant'])) {
                $const->setConstantReference($resolved['constant']);
            }
            if (isset($resolved['type'])) {
                $const->setType($resolved['type']);
            }

            $docComment = $node->getDocComment();
            if ($docComment !== NULL) {
                $block = $this->docBlockParser->parse($docComment, $this->aliasMap);
                $const->setDocBlock($block);
            }
        }

        private function processProperty(NodeType\Property $node) {
            $property = $node->props[0];
            $member = $this->unit->addMember($property->name);
            if ($node->props[0]->default) {
                $this->setVariableDefaultValue($member, $node->props[0]->default);
            }
            $visibility = 'public';
            if ($node->isPrivate()) {
                $visibility = 'private';
            } elseif ($node->isProtected()) {
                $visibility = 'protected';
            }
            $member->setVisibility($visibility);
            $member->setStatic($node->isStatic());
            $docComment = $node->getDocComment();
            if ($docComment !== NULL) {
                $block = $this->docBlockParser->parse($docComment, $this->aliasMap);
                $member->setDocBlock($block);
            }
            $member->setLine($node->getLine());
        }

        private function setVariableType(AbstractVariableObject $variable, $type = NULL) {
            if ($type instanceof \PhpParser\Node\NullableType) {
                $variable->setNullable(true);
                $type = $type->type;
            }

            if ($type === NULL) {
                $variable->setType('{unknown}');
                return;
            }

            if ($variable->isInternalType($type)) {
                $variable->setType($type);
                return;
            }

            if ($type instanceof \PhpParser\Node\Name\FullyQualified) {
                $variable->setType( (string)$type);
                return;
            }

            $type = (string)$type;
            if (isset($this->aliasMap[$type])) {
                $type = $this->aliasMap[$type];
            } elseif ($type[0]!=='\\') {
                $type = $this->namespace . '\\' . $type;
            }
            $variable->setType($type);
        }

        private function resolveExpressionValue(\PhpParser\Node\Expr $expr) {
            if ($expr instanceof \PhpParser\Node\Scalar\String_) {
                return array(
                    'type' => 'string',
                    'value' => $expr->getAttribute('originalValue')
                );
            }

            if ($expr instanceof \PhpParser\Node\Scalar\LNumber ||
                $expr instanceof \PhpParser\Node\Expr\UnaryMinus ||
                $expr instanceof \PhpParser\Node\Expr\UnaryPlus) {
                return array(
                    'type' => 'integer',
                    'value' => $expr->getAttribute('originalValue')
                );
            }

            if ($expr instanceof \PhpParser\Node\Scalar\DNumber) {
                return array(
                    'type' => 'float',
                    'value' => $expr->getAttribute('originalValue')
                );
            }

            if ($expr instanceof \PhpParser\Node\Expr\Array_) {
                return array(
                    'type' => 'array',
                    'value' => '' // @todo add array2xml?
                );
            }

            if ($expr instanceof \PhpParser\Node\Expr\ClassConstFetch) {
                return array(
                    'type' => '{unknown}',
                    'value' => '',
                    'constant' => implode('\\', $expr->class->parts) . '::' . $expr->name
                );
            }

            if ($expr instanceof \PhpParser\Node\Expr\ConstFetch) {
                $reference = implode('\\', $expr->name->parts);
                if (strtolower($reference) === 'null') {
                    return array(
                        'value' => 'NULL'
                    );
                }
                if (in_array(strtolower($reference), array('true', 'false'))) {
                    return array(
                        'type' => 'boolean',
                        'value' => $reference
                    );
                }
                return array(
                    'type' => '{unknown}',
                    'value' => '',
                    'constant' => implode('\\', $expr->name->parts)
                );
            }

            if ($expr instanceof \PhpParser\Node\Scalar\MagicConst\Line) {
                return array(
                    'type' => 'integer',
                    'value' => '',
                    'constant' => $expr->getName()
                );
            }

            if ($expr instanceof \PhpParser\Node\Scalar\MagicConst) {
                return array(
                    'type' => 'string',
                    'value' => '',
                    'constant' => $expr->getName()
                );
            }

            $type = get_class($expr);
            $line = $expr->getLine();
            $file = $this->result->getFileName();
            throw new ParseErrorException("Unexpected expression type '$type' for value in line $line of file '$file'", ParseErrorException::UnexpectedExpr);

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

            $resolved = $this->resolveExpressionValue($default);
            $variable->setDefault($resolved['value']);

            if (isset($resolved['type'])) {
                $variable->setType($resolved['type']);
            }

            if (isset($resolved['constant'])) {
                $variable->setConstant($resolved['constant']);
            }
        }

    }
}
