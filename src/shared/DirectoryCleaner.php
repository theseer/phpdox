<?php
namespace TheSeer\phpDox {

    class DirectoryCleaner {

        public function process(FileInfo $path) {
            if (strlen($path->getPathname()) < 5) {
                throw new DirectoryCleanerException(
                    'For security reasons, path must be at least 5 chars long',
                    DirectoryCleanerException::SecurityLimitation
                );
            }
            if (!file_exists($path)) {
                return;
            }
            $worker = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path->getPathname()));
            foreach($worker as $x) {
                if($x->getFilename() == "." || $x->getFilename() == "..") {
                    continue;
                }
                if ($x->isDir()) {
                    $this->clearDirectory(new FileInfo($x->getPathname()));
                }
                unlink($x->getPathname());
            }
        }
    }

    class DirectoryCleanerException extends \Exception {
        const SecurityLimitation = 1;
    }
}
