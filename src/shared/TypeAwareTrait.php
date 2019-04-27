<?php
namespace TheSeer\phpDox;

trait TypeAwareTrait
{
    public function getBuiltInTypes(int $context = TypeAwareInterface::TYPE_ALL): array {
        /*
         * From:
         * http://docs.phpdoc.org/guides/types.html#primitives
         * http://docs.phpdoc.org/guides/types.html#keywords
         */
        $docblockType = ['string', 'int', 'integer', 'float', 'boolean', 'bool', 'array', 'resource', 'null', 'callable',
            'mixed', 'void', 'object', 'true', 'false', 'self', 'static', '$this'];

        $phpdoxType = ['', '{unknown}'];

        /*
         * From:
         * https://www.php.net/manual/en/functions.arguments.php#functions.arguments.type-declaration
         * + callable (https://www.php.net/manual/en/migration70.new-features.php#migration70.new-features.scalar-type-declarations)
         */
        $variableType = ['object', 'array', 'int',
            'float', 'string', 'callable', 'iterable', 'object', 'self', 'bool'];
        /*
         * From:
         * https://www.php.net/manual/en/functions.returning-values.php#functions.returning-values.type-declaration
         * + void (https://www.php.net/manual/en/migration71.new-features.php#migration71.new-features.void-functions)
         */
        $returnType = $variableType + ['void'];

        $type = [];

        switch (true) {
            case ($context & TypeAwareInterface::TYPE_RETURN) !== 0:
                $type = array_merge($type, $returnType);
                // no-break
            case ($context & TypeAwareInterface::TYPE_PHPDOC) !== 0:
                $type = array_merge($type, $docblockType);
                // no-break
            case ($context & TypeAwareInterface::TYPE_BUILTIN) !== 0:
                $type = array_merge($type, $variableType);
                // no-break
            case ($context & TypeAwareInterface::TYPE_PHPDOX) !== 0:
                $type = array_merge($type, $phpdoxType);
                // no-break
        }

        return array_unique($type);
    }
    public function isBuiltInType(string $type, int $context = TypeAwareInterface::TYPE_ALL): bool {
        return \in_array(\mb_strtolower($type), $this->getBuiltInTypes($context), true);
    }
}
