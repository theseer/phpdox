<?php
namespace TheSeer\phpDox\Collector {

    use TheSeer\phpDox\Collector\Backend\SourceFileException;
    use TheSeer\phpDox\FileInfo;

    class SourceFile {

        /**
         * @var FileInfo
         */
        private $fileInfo;

        public function __construct(FileInfo $fileInfo) {
            $this->fileInfo = $fileInfo;
        }

        /**
         * @return FileInfo
         */
        public function getFileInfo() {
            return $this->fileInfo;
        }

        /**
         * @return string
         *
         * @throws Backend\SourceFileException
         */
        public function getSource() {
            $code = file_get_contents($this->fileInfo->getPathname());

            $info = new \finfo();
            $encoding = $info->file($this->fileInfo, FILEINFO_MIME_ENCODING);
            if (strtolower($encoding) != 'utf-8') {
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

            return $cleanCode;
        }

    }

}
