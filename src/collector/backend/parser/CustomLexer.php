<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Collector\Backend;

use PhpParser\Lexer\Emulative;
use PhpParser\Parser\Tokens;

/**
 * CustomLexer as suggest for workaround for issue 26 (https://github.com/nikic/PHP-Parser/issues/26)
 */
class CustomLexer extends Emulative {
    public function getNextToken(&$value = null, &$startAttributes = null, &$endAttributes = null): int {
        $tokenId = parent::getNextToken($value, $startAttributes, $endAttributes);

        if ($tokenId == Tokens::T_CONSTANT_ENCAPSED_STRING
            || $tokenId == Tokens::T_LNUMBER
            || $tokenId == Tokens::T_DNUMBER
        ) {
            $endAttributes['originalValue'] = $value;
        }

        return $tokenId;
    }

    protected function resetErrors(): void {
        // kill PHPParser_Lexer's Error reset code as it breaks phpDox's error handling
    }
}
