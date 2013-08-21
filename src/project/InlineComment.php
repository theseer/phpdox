<?php
namespace TheSeer\phpDox\Project {

    use TheSeer\fDOM\fDOMDocument;
    use TheSeer\fDOM\fDOMElement;

    class InlineComment {

        const XMLNS = 'http://xml.phpdox.de/src#';

        private $fragment;
        private $startLine;

        public function __construct($line, $comment) {
            $this->startLine = $line;

            $dom = new fDOMDocument();
            $this->fragment = $dom->createDocumentFragment();
            $this->parse(
                $this->normalizeSplit($comment)
            );
        }

        public function asDom(fDOMDocument $dom) {
            return $dom->importNode($this->fragment, true);
        }

        private function normalizeSplit($comment) {
            $comment = str_replace(array("\r\n", "\n"), "\n", $comment);
            $res = array();
            foreach(explode("\n", trim($comment)) as $line) {
                $line = trim($line);
                preg_match('=//(.*)$|/\*{1,}(.*)\*/$|/\*{1,}(.*)$|^(.*)\*/$|#(.*)$|\*{1}(.*)$|^(.*)$=', $line, $matches);
                $normalized = trim(end($matches));
                if ($normalized != '') {
                    $res[] = $normalized;
                }
            }
            return $res;
        }

        private function parse(array $comments) {
            foreach($comments as $pos => $comment) {
                preg_match('=^@{0,1}(todo|var):{0,1}(.*)=i', $comment, $matches);
                if (count($matches) != 0) {
                    switch(strtolower($matches[1])) {
                        case 'var': {
                            // we ignore @var comments as they are IDE support only
                            continue;
                        }
                        case 'todo': {
                            $node = $this->fragment->appendChild(
                                $this->fragment->ownerDocument->createElementNS(self::XMLNS, 'todo')
                            );
                            $node->appendChild(
                                $this->fragment->ownerDocument->createTextNode(trim($matches[2]))
                            );
                            break;
                        }
                    }
                } else {
                    $node = $this->fragment->appendChild(
                        $this->fragment->ownerDocument->createElementNS(self::XMLNS, 'comment')
                    );
                    $node->appendChild(
                        $this->fragment->ownerDocument->createTextNode(trim($comment))
                    );
                }
                /*
                if ($node instanceof fDOMElement) {
                    $node->setAttribute('line', $this->startLine + $pos);
                }
                */
            }
        }

    }

}
