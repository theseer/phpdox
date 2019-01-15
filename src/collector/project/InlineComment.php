<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Collector;

use TheSeer\fDOM\fDOMDocument;
use TheSeer\fDOM\fDOMElement;

class InlineComment {
    public const XMLNS = 'http://xml.phpdox.net/src';

    private $fragment;

    private $startLine;

    public function __construct($line, $comment) {
        $this->startLine = $line;

        $this->fragment = (new fDOMDocument())->createDocumentFragment();
        $this->parse(
            $this->normalizeSplit($comment)
        );
    }

    public function getCount() {
        return $this->fragment->childNodes->length;
    }

    public function asDom(fDOMDocument $dom) {
        return $dom->importNode($this->fragment, true);
    }

    private function normalizeSplit($comment) {
        $comment = \str_replace(["\r\n", "\n"], "\n", $comment);
        $res     = [];

        foreach (\explode("\n", \trim($comment)) as $line) {
            $line = \trim($line);
            \preg_match('=//(.*)$|/\*{1,}(.*)\*/$|/\*{1,}(.*)$|^(.*)\*/$|#(.*)$|\*{1}(.*)$|^(.*)$=', $line, $matches);
            $normalized = \trim(\end($matches));

            if ($normalized != '') {
                $res[] = $normalized;
            }
        }

        return $res;
    }

    private function parse(array $comments): void {
        foreach ($comments as $pos => $comment) {
            \preg_match('=^@{0,1}(todo|var|fixme):{0,1}(.*)=i', $comment, $matches);

            if (\count($matches) != 0) {
                switch (\mb_strtolower($matches[1])) {
                    case 'var':
                        {
                            // we ignore @var comments as they are IDE support only
                            continue 2;
                        }
                    case 'fixme':
                    case 'todo':
                        {
                            $node = $this->fragment->appendChild(
                                $this->fragment->ownerDocument->createElementNS(self::XMLNS, \mb_strtolower($matches[1]))
                            );
                            $node->setAttribute('value', \trim($matches[2]));

                            break;
                        }
                }
            } else {
                $node = $this->fragment->appendChild(
                    $this->fragment->ownerDocument->createElementNS(self::XMLNS, 'comment')
                );
                $node->setAttribute('value', \trim($comment));
            }

            if (isset($node) && $node instanceof fDOMElement) {
                $node->setAttribute('line', $this->startLine + $pos);
            }
        }
    }
}
