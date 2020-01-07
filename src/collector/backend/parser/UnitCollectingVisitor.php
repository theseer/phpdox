<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Collector\Backend;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Expr\UnaryPlus;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\MagicConst;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt as NodeType;
use PhpParser\NodeVisitorAbstract;
use TheSeer\phpDox\Collector\AbstractUnitObject;
use TheSeer\phpDox\Collector\AbstractVariableObject;
use TheSeer\phpDox\Collector\InlineComment;
use TheSeer\phpDox\Collector\MethodObject;
use TheSeer\phpDox\DocBlock\Parser as DocBlockParser;
use TheSeer\phpDox\TypeAwareInterface;
use TheSeer\phpDox\TypeAwareTrait;

class UnitCollectingVisitor extends NodeVisitorAbstract implements TypeAwareInterface {
    use TypeAwareTrait;

    /**
     * @var \TheSeer\phpDox\DocBlock\Parser
     */
    private $docBlockParser;

    /**
     * @var array
     */
    private $aliasMap = [];

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

    private $modifier = [
        NodeType\Class_::MODIFIER_PUBLIC    => 'public',
        NodeType\Class_::MODIFIER_PROTECTED => 'protected',
        NodeType\Class_::MODIFIER_PRIVATE   => 'private',
    ];

    public function __construct(DocBlockParser $parser, ParseResult $result) {
        $this->docBlockParser = $parser;
        $this->result         = $result;
    }

    /**
     * @return null|int|\PhpParser\Node|void
     */
    public function enterNode(\PhpParser\Node $node) {
        if ($node instanceof NodeType\Namespace_ && $node->name != null) {
            $this->namespace             = \implode('\\', $node->name->parts);
            $this->aliasMap['::context'] = $this->namespace;
        } else {
            if ($node instanceof NodeType\UseUse) {
                $this->aliasMap[$node->getAlias()->name] = \implode('\\', $node->name->parts);
            } else {
                if ($node instanceof NodeType\Class_) {
                    $this->aliasMap['::unit'] = (string)$node->namespacedName;
                    $this->unit               = $this->result->addClass((string)$node->namespacedName);
                    $this->processUnit($node);

                    return;
                }

                if ($node instanceof NodeType\Interface_) {
                    $this->aliasMap['::unit'] = (string)$node->namespacedName;
                    $this->unit               = $this->result->addInterface((string)$node->namespacedName);
                    $this->processUnit($node);

                    return;
                }

                if ($node instanceof NodeType\Trait_) {
                    $this->aliasMap['::unit'] = (string)$node->namespacedName;
                    $this->unit               = $this->result->addTrait((string)$node->namespacedName);
                    $this->processUnit($node);

                    return;
                }

                if ($node instanceof NodeType\Property) {
                    $this->processProperty($node);

                    return;
                }

                if ($node instanceof NodeType\ClassMethod) {
                    $this->processMethod($node);

                    return;
                }

                if ($node instanceof NodeType\ClassConst) {
                    $this->processClassConstant($node);
                } elseif ($node instanceof NodeType\TraitUse) {
                    $this->processTraitUse($node);
                }
            }
        }
    }

    /**
     * @return null|false|int|\PhpParser\Node|\PhpParser\Node[]|void
     */
    public function leaveNode(\PhpParser\Node $node) {
        if ($node instanceof NodeType\Class_
            || $node instanceof NodeType\Interface_
            || $node instanceof NodeType\Trait_) {
            $this->unit = null;

            return;
        }
    }

    /**
     * @param $node
     */
    private function processUnit($node): void {
        $this->unit->setStartLine($node->getAttribute('startLine'));
        $this->unit->setEndLine($node->getAttribute('endLine'));

        if ($node instanceof NodeType\Class_) {
            $this->unit->setAbstract($node->isAbstract());
            $this->unit->setFinal($node->isFinal());
        } else {
            $this->unit->setAbstract(false);
            $this->unit->setFinal(false);
        }

        $docComment = $node->getDocComment();

        if ($docComment !== null) {
            $block = $this->docBlockParser->parse($docComment, $this->aliasMap);
            $this->unit->setDocBlock($block);
        }

        if ($node->getType() != 'Stmt_Trait' && $node->extends !== null) {
            if (\is_array($node->extends)) {
                $extendsArray = $node->extends;

                foreach ($extendsArray as $extends) {
                    $this->unit->addExtends(\implode('\\', $extends->parts));
                }
            } else {
                $this->unit->addExtends(\implode('\\', $node->extends->parts));
            }
        }

        if ($node->getType() === 'Stmt_Class') {
            foreach ($node->implements as $implements) {
                $this->unit->addImplements(\implode('\\', $implements->parts));
            }
        }
    }

    private function processTraitUse(NodeType\TraitUse $node): void {
        foreach ($node->traits as $trait) {
            $traitUse = $this->unit->addTrait((string)$trait);
            $traitUse->setStartLine($node->getAttribute('startLine'));
            $traitUse->setEndLine($node->getAttribute('endLine'));
        }

        foreach ($node->adaptations as $adaptation) {
            if ($adaptation instanceof NodeType\TraitUseAdaptation\Alias) {
                if ($adaptation->trait instanceof FullyQualified) {
                    $traitUse = $this->getTraitUse((string)$adaptation->trait);
                } else {
                    if (\count($node->traits) === 1) {
                        $traitUse = $this->getTraitUse((string)$node->traits[0]);
                    } else {
                        $traitUse = $this->unit->getAmbiguousTraitUse();
                    }
                }

                $traitUse->addAlias(
                    $adaptation->method,
                    $adaptation->newName,
                    $adaptation->newModifier ? $this->modifier[$adaptation->newModifier] : null
                );

                continue;
            }

            if ($adaptation instanceof NodeType\TraitUseAdaptation\Precedence) {
                $traitUse = $this->getTraitUse((string)$adaptation->insteadof[0]);
                $traitUse->addExclude($adaptation->method);

                continue;
            }

            throw new ParseErrorException(
                \sprintf('Unexpected adaption type %s', \get_class($adaptation)),
                ParseErrorException::UnexpectedExpr
            );
        }
    }

    private function getTraitUse($traitName) {
        if (!$this->unit->usesTrait($traitName)) {
            throw new ParseErrorException(
                \sprintf('Referenced trait "%s" not used', $traitName),
                ParseErrorException::GeneralParseError
            );
        }

        return $this->unit->getTraitUse($traitName);
    }

    private function processMethod(NodeType\ClassMethod $node): void {

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

        if ($docComment !== null) {
            $block = $this->docBlockParser->parse($docComment, $this->aliasMap);
            $method->setDocBlock($block);
        }

        $this->processMethodParams($method, $node->params);

        if ($node->stmts) {
            $this->processInlineComments($method, $node->stmts);
        }
    }

    private function processMethodReturnType(MethodObject $method, $returnType): void {
        if ($returnType === null) {
            return;
        }

        $typeStr = $returnType instanceof NullableType
            ? ((string)$returnType->type)
            : ((string)$returnType);
        if ($this->isBuiltInType($typeStr, self::TYPE_RETURN)) {
            $returnTypeObject = $method->setReturnType($returnType);
            $returnTypeObject->setNullable(false);

            return;
        }

        if ($returnType instanceof \PhpParser\Node\Name\FullyQualified) {
            $returnTypeObject = $method->setReturnType($returnType->toString());
            $returnTypeObject->setNullable(false);

            return;
        }

        if ($returnType instanceof NullableType) {
            if ((string)$returnType->type === 'self') {
                $returnTypeObject = $method->setReturnType($this->unit->getName());
            } else {
                $returnTypeObject = $method->setReturnType($returnType->type);
            }
            $returnTypeObject->setNullable(true);

            return;
        }

        if ($returnType instanceof \PhpParser\Node\Name) {
            $returnTypeObject = $method->setReturnType(
                $this->unit->getName()
            );
            $returnTypeObject->setNullable(false);

            return;
        }

        throw new ParseErrorException(
            \sprintf(
                'Unexpected return type definition %s',
                \get_class($returnType)
            ),
            ParseErrorException::UnexpectedExpr
        );
    }

    private function processInlineComments(MethodObject $method, array $stmts): void {
        foreach ($stmts as $stmt) {
            if ($stmt->hasAttribute('comments')) {
                foreach ($stmt->getAttribute('comments') as $comment) {
                    $inline = new InlineComment($comment->getLine(), $comment->getText());

                    if ($inline->getCount() != 0) {
                        $method->addInlineComment($inline);
                    }
                }
            }
        }
    }

    private function processMethodParams(MethodObject $method, array $params): void {
        foreach ($params as $param) {
            /** @var $param \PhpParser\Node\Param */
            $parameter = $method->addParameter($param->var->name);
            $parameter->setByReference($param->byRef);
            $parameter->setVariadic($param->variadic);
            $this->setVariableType($parameter, $param->type);
            $this->setVariableDefaultValue($parameter, $param->default);
        }
    }

    private function processClassConstant(NodeType\ClassConst $node): void {
        $constNode = $node->consts[0];
        $const     = $this->unit->addConstant($constNode->name);

        $resolved = $this->resolveExpressionValue($constNode->value);

        $const->setValue($resolved['value']);

        if (isset($resolved['constant'])) {
            $const->setConstantReference($resolved['constant']);
        }

        if (isset($resolved['type'])) {
            $const->setType($resolved['type']);
        }

        $docComment = $node->getDocComment();

        if ($docComment !== null) {
            $block = $this->docBlockParser->parse($docComment, $this->aliasMap);
            $const->setDocBlock($block);
        }
    }

    private function processProperty(NodeType\Property $node): void {
        $property = $node->props[0];
        $member   = $this->unit->addMember($property->name);

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

        if ($docComment !== null) {
            $block = $this->docBlockParser->parse($docComment, $this->aliasMap);
            $member->setDocBlock($block);
        }
        $member->setLine($node->getLine());
    }

    private function setVariableType(AbstractVariableObject $variable, $type = null): void {
        if ($type instanceof NullableType) {
            $variable->setNullable(true);
            $type = $type->type;
        }

        if ($type === null) {
            $variable->setType('{unknown}');

            return;
        }

        if ($variable->isInternalType($type)) {
            $variable->setType($type);

            return;
        }

        if ($type instanceof \PhpParser\Node\Name\FullyQualified) {
            $variable->setType((string)$type);

            return;
        }

        $type = (string)$type;

        if (isset($this->aliasMap[$type])) {
            $type = $this->aliasMap[$type];
        } elseif ($type[0] !== '\\') {
            $type = $this->namespace . '\\' . $type;
        }
        $variable->setType($type);
    }

    private function resolveExpressionValue(Expr $expr) {
        if ($expr instanceof String_) {
            return [
                'type'  => 'string',
                'value' => $expr->getAttribute('originalValue')
            ];
        }

        if ($expr instanceof LNumber ||
            $expr instanceof UnaryMinus ||
            $expr instanceof UnaryPlus) {
            return [
                'type'  => 'integer',
                'value' => $expr->getAttribute('originalValue')
            ];
        }

        if ($expr instanceof DNumber) {
            return [
                'type'  => 'float',
                'value' => $expr->getAttribute('originalValue')
            ];
        }

        if ($expr instanceof Array_) {
            return [
                'type'  => 'array',
                'value' => '' // @todo add array2xml?
            ];
        }

        if ($expr instanceof ClassConstFetch) {
            return [
                'type'     => '{unknown}',
                'value'    => '',
                'constant' => \implode('\\', $expr->class->parts) . '::' . $expr->name
            ];
        }

        if ($expr instanceof ConstFetch) {
            $reference = \implode('\\', $expr->name->parts);

            if (\strtolower($reference) === 'null') {
                return [
                    'value' => 'NULL'
                ];
            }

            if (\in_array(\strtolower($reference), ['true', 'false'])) {
                return [
                    'type'  => 'boolean',
                    'value' => $reference
                ];
            }

            return [
                'type'     => '{unknown}',
                'value'    => '',
                'constant' => \implode('\\', $expr->name->parts)
            ];
        }

        if ($expr instanceof MagicConst\Line) {
            return [
                'type'     => 'integer',
                'value'    => '',
                'constant' => $expr->getName()
            ];
        }

        if ($expr instanceof MagicConst) {
            return [
                'type'     => 'string',
                'value'    => '',
                'constant' => $expr->getName()
            ];
        }

        if ($expr instanceof BinaryOp) {
            $code = (new \PhpParser\PrettyPrinter\Standard)->prettyPrint([$expr]);

            return [
                'type'  => 'expression',
                'value' => $code
            ];
        }

        $type = \get_class($expr);
        $line = $expr->getLine();
        $file = $this->result->getFileName();

        throw new ParseErrorException("Unexpected expression type '$type' for value in line $line of file '$file'", ParseErrorException::UnexpectedExpr);
    }

    /**
     * @param Expr $default
     *
     * @throws ParseErrorException
     */
    private function setVariableDefaultValue(AbstractVariableObject $variable, Expr $default = null): void {
        if ($default === null) {
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
