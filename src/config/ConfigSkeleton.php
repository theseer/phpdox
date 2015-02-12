<?php
namespace TheSeer\phpDox {

    use TheSeer\fDOM\fDOMDocument;

    class ConfigSkeleton {

        /**
         * @var FileInfo
         */
        private $file;

        /**
         * @param FileInfo $file
         */
        public function __construct(FileInfo $file) {
            $this->file = $file;
        }

        /**
         * @return string
         */
        public function render() {
            return file_get_contents($this->file->getPathname());
        }

        /**
         * @return string
         */
        public function renderStripped() {
            $dom = new fDOMDocument();
            $dom->preserveWhiteSpace = FALSE;
            $dom->loadXML(
                preg_replace("/\s{2,}/u", " ", $this->render())
            );
            foreach($dom->query('//comment()') as $c) {
                $c->parentNode->removeChild($c);
            }
            $dom->formatOutput = TRUE;
            return $dom->saveXML();
        }

    }

}
