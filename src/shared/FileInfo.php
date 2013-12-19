<?php
namespace TheSeer\phpDox {

    class FileInfo extends \SplFileInfo {

        public function getRealPath() {
            $path = parent::getRealPath();
            if (!$path) {
                throw new FileInfoException(
                    sprintf("Path '%s' does not exist - call to realpath failed", $this->getPathname()),
                    FileInfoException::InvalidPath
                );
            }
            return $this->toUnix($path);
        }

        public function asFileUri() {
            $result = $this->getRealPath();
            if ($result[0] != '/') {
                $result = '/' . $result;
            }
            return 'file://' . $result;
        }

        public function getPath() {
            return $this->toUnix(parent::getPath());
        }

        public function getRelative(\SplFileInfo $relation) {
            $relPath = $this->getRealPath();
            $relPath = substr($relPath, strlen(dirname($relation->getRealPath()))+1);
            return new FileInfo($relPath);
        }

        public function getPathname() {
            return $this->toUnix(parent::getPathname());
        }

        public function getLinkTarget() {
            return $this->toUnix(parent::getLinkTarget());
        }

        public function __toString() {
            return $this->getPathname();
        }

        public function getFileInfo($class_name = NULL) {
            throw new FileInfoException("getFileInfo not implemented", FileInfoException::NotImplemented);
        }

        public function getPathInfo($class_name = NULL) {
            throw new FileInfoException("getPathInfo not implemented", FileInfoException::NotImplemented);
        }

        private function toUnix($str) {
            return str_replace('\\', '/', $str);
        }

    }

    class FileInfoException extends \Exception {
        const InvalidPath = 1;
        const NotImplemented = 2;
    }
}
