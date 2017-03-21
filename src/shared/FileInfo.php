<?php
namespace TheSeer\phpDox {

    class FileInfo extends \SplFileInfo {

        /**
         * @return mixed
         * @throws FileInfoException
         */
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

        /**
         * @return bool
         */
        public function exists() {
            clearstatcache(true, $this->getPathname());
            return file_exists($this->getPathname());
        }

        /**
         * @return string
         */
        public function asFileUri() {
            $result = $this->getRealPath();
            if ($result[0] !== '/') {
                $result = '/' . $result;
            }
            return 'file://' . $result;
        }

        /**
         * @return mixed
         */
        public function getPath() {
            return $this->toUnix(parent::getPath());
        }

        /**
         * @param \SplFileInfo $relation
         * @param bool         $inclusive
         *
         * @return FileInfo
         */
        public function getRelative(\SplFileInfo $relation, $inclusive = TRUE) {
            $relPath = $this->getRealPath();
            $relationPath = $relation->getRealPath();
            if ($inclusive) {
                $relationPath = dirname($relationPath);
            }
            $relPath = mb_substr($relPath, mb_strlen($relationPath)+1);
            return new FileInfo($relPath);
        }

        /**
         * @return string
         */
        public function getPathname() {
            return $this->toUnix(parent::getPathname());
        }

        /**
         * @return string
         */
        public function getLinkTarget() {
            return $this->toUnix(parent::getLinkTarget());
        }

        /**
         * @return string
         */
        public function __toString() {
            return $this->getPathname();
        }

        /**
         * @param string $class_name
         *
         * @throws FileInfoException
         */
        public function getFileInfo($class_name = NULL) {
            throw new FileInfoException("getFileInfo not implemented", FileInfoException::NotImplemented);
        }

        /**
         * @param string $class_name
         *
         * @throws FileInfoException
         */
        public function getPathInfo($class_name = NULL) {
            throw new FileInfoException("getPathInfo not implemented", FileInfoException::NotImplemented);
        }

        /**
         * @param string $str
         *
         * @return string
         */
        private function toUnix($str) {
            return str_replace('\\', '/', $str);
        }

    }

}
