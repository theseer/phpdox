<?php
namespace TheSeer\phpDox {

    class DirectoryCleaner {

        public function process(FileInfo $path) {
            if (mb_strlen($path->getPathname()) < 5) {
                throw new DirectoryCleanerException(
                    'For security reasons, path must be at least 5 chars long',
                    DirectoryCleanerException::SecurityLimitation
                );
            }

            if (!$path->exists()) {
                return;
            }

            $worker = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(
                    $path->getPathname(),
                    \FilesystemIterator::SKIP_DOTS
                ),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach($worker as $entry) {
                if ($entry->isDir() && !$entry->isLink()) {
                    rmdir($entry->getPathname());
                } else {
                    unlink($entry->getPathname());
                }
            }
            rmdir($path);
        }

    }

}
