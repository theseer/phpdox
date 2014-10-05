<?php
namespace TheSeer\phpDox\Generator {

    use TheSeer\fDOM\fDOMDocument;
    use TheSeer\phpDox\Collector\SourceFile;
    use TheSeer\phpDox\FileInfo;

    class TokenFile {

        /**
         * @var FileInfo
         */
        private $file;

        private $dom;

        public function __construct(FileInfo $file) {
            if (!$file->exists()) {
                throw new TokenFileException(
                    sprintf("File '%s' not found", $file->getPathname()),
                    TokenFileException::FileNotFound
                );
            }
            $this->file = $file;
        }

        public function getRelativeName(FileInfo $path) {
            $file = new FileInfo($this->asDom()->getElementsByTagNameNS(SourceFile::XMLNS, 'file')->item(0)->getAttribute('realpath'));
            return $file->getRelative($path, FALSE);
        }

        public function asDom() {
            if (!$this->dom instanceof fDOMDocument) {
                $this->dom = new fDOMDocument();
                $this->dom->load($this->file->getPathname());
                $this->dom->registerNamespace('phpdox', SourceFile::XMLNS);
            }
            return $this->dom;
        }

    }

    class TokenFileException extends \Exception {
        const FileNotFound = 1;
    }

}
