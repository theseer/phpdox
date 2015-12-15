<?php
namespace TheSeer\phpDox\Collector {

    use TheSeer\fDOM\fDOMDocument;

    class Tokenizer {

        /**
         * @var \XMLWriter
         */
        private $writer;

        /**
         * @var int
         */
        private $lastLine = 1;

        /**
         * Token Map for "non-tokens"
         *
         * @var array
         */
        private $map = array(
            '(' => 'T_PHPDOX_OPEN_BRACKET',
            ')' => 'T_PHPDOX_CLOSE_BRACKET',
            '[' => 'T_PHPDOX_OPEN_SQUARE',
            ']' => 'T_PHPDOX_CLOSE_SQUARE',
            '{' => 'T_PHPDOX_OPEN_CURLY',
            '}' => 'T_PHPDOX_CLOSE_CURLY',
            ';' => 'T_PHPDOX_SEMICOLON',
            '.' => 'T_PHPDOX_DOT',
            ',' => 'T_PHPDOX_COMMA',
            '=' => 'T_PHPDOX_EQUAL',
            '<' => 'T_PHPDOX_LT',
            '>' => 'T_PHPDOX_GT',
            '+' => 'T_PHPDOX_PLUS',
            '-' => 'T_PHPDOX_MINUS',
            '*' => 'T_PHPDOX_MULT',
            '/' => 'T_PHPDOX_DIV',
            '?' => 'T_PHPDOX_QUESTION_MARK',
            '!' => 'T_PHPDOX_EXCLAMATION_MARK',
            ':' => 'T_PHPDOX_COLON',
            '"' => 'T_PHPDOX_DOUBLE_QUOTES',
            '@' => 'T_PHPDOX_AT',
            '&' => 'T_PHPDOX_AMPERSAND',
            '%' => 'T_PHPDOX_PERCENT',
            '|' => 'T_PHPDOX_PIPE',
            '$' => 'T_PHPDOX_DOLLAR',
            '^' => 'T_PHPDOX_CARET',
            '~' => 'T_PHPDOX_TILDE',
            '`' => 'T_PHPDOX_BACKTICK'
        );

        /**
         * @param string $source
         *
         * @return fDOMDocument
         *
         * @throws \TheSeer\fDOM\fDOMException
         */
        public function toXML($source) {

            $this->writer = new \XMLWriter();
            $this->writer->openMemory();
            $this->writer->setIndent(true);
            $this->writer->startDocument();
            $this->writer->startElement('source');
            $this->writer->writeAttribute('xmlns', 'http://xml.phpdox.net/token');
            $this->writer->startElement('line');
            $this->writer->writeAttribute('no', 1);

            $this->lastLine = 1;
            $tokens = token_get_all($source);

            foreach($tokens as $pos => $tok) {
                if (is_string($tok)) {
                    $line = 1;
                    $step = 1;
                    while (!is_array($tokens[$pos - $step])) {
                        $step++;
                        if (($pos - $step) == -1) {
                            break;
                        }
                    }
                    if ($pos - $step != -1) {
                        $line = $tokens[$pos - $step][2];
                        $line += count(preg_split('/\R+/', $tokens[$pos - $step][1])) - 1;
                    }
                    $token = array(
                        'name' => $this->map[$tok],
                        'value' => $tok,
                        'line' => $line
                    );
                    $this->addToken($token);
                } else {
                    $line = $tok[2];
                    $values = preg_split('/\R+/Uu', $tok[1]);

                    foreach($values as $v) {
                        $token = array(
                            'name' => token_name($tok[0]),
                            'value' => $v,
                            'line' => $line
                        );
                        $this->addToken($token);
                        $line++;
                    }
                }
            }

            $this->writer->endElement();
            $this->writer->endElement();
            $this->writer->endDocument();

            $dom = new fDOMDocument();
            $dom->preserveWhiteSpace = false;
            $dom->loadXML($this->writer->outputMemory());
            return $dom;
        }

        private function addToken(array $token) {
            if ($this->lastLine < $token['line']) {
                $this->writer->endElement();

                for($t=$this->lastLine + 1; $t<$token['line']; $t++) {
                    $this->writer->startElement('line');
                    $this->writer->writeAttribute('no', $t);
                    $this->writer->endElement();
                }
                $this->writer->startElement('line');
                $this->writer->writeAttribute('no', $token['line']);
                $this->lastLine = $token['line'];
            }

            if ($token['value'] != '') {
                $this->writer->startElement('token');
                $this->writer->writeAttribute('name', $token['name']);
                $this->writer->writeRaw( htmlspecialchars($token['value'], ENT_NOQUOTES | ENT_DISALLOWED | ENT_XML1) );
                $this->writer->endElement();
            }
        }
    }

}
