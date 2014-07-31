<?php
namespace TheSeer\phpDox\Collector {

    use TheSeer\phpDox\Collector\Backend\SourceFileException;
    use TheSeer\phpDox\FileInfo;

    class SourceFile extends FileInfo {

        /**
         * @var string
         */
        private $src;

        /**
         * @return string
         *
         * @throws Backend\SourceFileException
         */
        public function getSource() {
            if ($this->src !== NULL) {
                return $this->src;
            }

            $code = file_get_contents($this->getPathname());

            $info = new \finfo();
            $encoding = $info->file( (string)$this, FILEINFO_MIME_ENCODING);
            if (strtolower($encoding) != 'utf-8' && $code != '') {
                try {
                    $code = iconv($encoding, 'UTF-8//TRANSLIT', $code);
                } catch (\ErrorException $e) {
                    throw new SourceFileException('Encoding error - conversion to UTF-8 failed', SourceFileException::BadEncoding, $e);
                }
            }

            // This is a workaround to filter out leftover invalid UTF-8 byte sets
            // even if the source looks like it's UTF-8 already
            mb_substitute_character('none');
            $cleanCode = mb_convert_encoding($code, 'UTF-8', 'UTF-8');
            if ($cleanCode != $code) {
                throw new SourceFileException('Encoding error - invalid UTF-8 bytes found', SourceFileException::InvalidDataBytes);
            }

            // Replace xml relevant control characters by surrogates
            $this->src = preg_replace_callback(
                '/(?![\x{000d}\x{000a}\x{0009}])\p{C}/u',
                function(array $matches) {
                    $unicodeChar = '\u' . (2400 + ord($matches[0]));
                    return json_decode('"'.$unicodeChar.'"');
                },
                $cleanCode
            );

            return $this->src;
        }

        /**
         * @return \TheSeer\fDOM\fDOMDocument
         *
         * @throws Backend\SourceFileException
         */
        public function getTokens() {
            $tokenizer = new Tokenizer();
            return $tokenizer->toXML($this->getSource());
        }

    }

}
