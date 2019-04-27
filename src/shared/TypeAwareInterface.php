<?php
namespace TheSeer\phpDox;

interface TypeAwareInterface
{
    public const TYPE_RETURN = 1;
    public const TYPE_PHPDOC = 2;
    public const TYPE_BUILTIN = 4;
    public const TYPE_PHPDOX = 8;
    public const TYPE_ALL = self::TYPE_RETURN + self::TYPE_PHPDOC + self::TYPE_BUILTIN + self::TYPE_PHPDOX;

    public function getBuiltInTypes(int $context = self::TYPE_ALL): array;
    public function isBuiltInType(string $type, int $context = self::TYPE_ALL): bool;
}
