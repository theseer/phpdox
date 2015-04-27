<?php
namespace TheSeer\phpDox\Collector {

    use TheSeer\fDOM\fDOMDocument;
    use TheSeer\fDOM\fDOMElement;
    use TheSeer\phpDox\Collector\Backend\SourceFileException;
    use TheSeer\phpDox\FileInfo;

    class SourceFile extends FileInfo {

        /**
         * PHPDOX Namespace
         */
        const XMLNS = 'http://xml.phpdox.net/src';

        /**
         * @var string
         */
        private $src;

        /**
         * @var FileInfo
         */
        private $srcDir;

        /**
         * @var
         */
        private $encoding;

        public function __construct($file_name, FileInfo $srcDir = NULL, $encoding = 'auto') {
            parent::__construct($file_name);
            $this->srcDir = $srcDir;
            $this->encoding = $encoding;
        }

        /**
         * @return string
         *
         * @throws Backend\SourceFileException
         */
        public function getSource() {
            if ($this->src !== NULL) {
                return $this->src;
            }

            $source = file_get_contents($this->getPathname());
            if ($source == '') {
                $this->src = '';
                return '';
            }

            if ($this->encoding == 'auto') {
                $info = new \finfo();
                $this->encoding = $info->file((string)$this, FILEINFO_MIME_ENCODING);
            }
            try {
                $source = iconv($this->encoding, 'UTF-8//TRANSLIT', $source);
            } catch (\ErrorException $e) {
                throw new SourceFileException('Encoding error - conversion to UTF-8 failed', SourceFileException::BadEncoding, $e);
            }

            // Replace xml relevant control characters by surrogates
            $this->src = preg_replace_callback(
                '/(?![\x{000d}\x{000a}\x{0009}])\p{C}/u',
                function(array $matches) {
                    $unicodeChar = '\u' . (2400 + ord($matches[0]));
                    return json_decode('"'.$unicodeChar.'"');
                },
                $source
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
            $dom = $tokenizer->toXML($this->getSource());
            $root = $dom->documentElement;
            $root->insertBefore($this->asNode($dom->documentElement), $root->firstChild);
            return $dom;
        }

        /**
         * @param fDOMDocument $ctx
         *
         * @return \TheSeer\fDOM\fDOMElement
           */
        public function asNode(fDOMElement $ctx) {
            $fileNode = $ctx->ownerDocument->createElementNS(self::XMLNS, 'file');
            $fileNode->setAttribute('path', $this->getPath());
            $fileNode->setAttribute('file', $this->getBasename());
            $fileNode->setAttribute('realpath', $this->getRealPath());
            $fileNode->setAttribute('size', $this->getSize());
            $fileNode->setAttribute('time', date('c', $this->getMTime()));
            $fileNode->setAttribute('unixtime', $this->getMTime());
            $fileNode->setAttribute('sha1', sha1_file($this->getRealPath()));
            if ($this->srcDir instanceof FileInfo) {
                $fileNode->setAttribute('relative', $this->getRelative($this->srcDir, FALSE));
            }
            return $fileNode;
        }

    }

}
