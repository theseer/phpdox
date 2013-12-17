<?php
namespace TheSeer\phpDox {

    class Path {

        /**
         * @var \SplFileInfo
         */
        private $dir;

        public function __construct($dir) {
            $this->dir = new \SplFileInfo($dir);
        }

        public function getRealPath() {
            return $this->toUnix($this->dir->getRealPath());
        }

        public function __toString() {
            return $this->toUnix( (string)$this->dir);
        }

        private function toUnix($str) {
            return str_replace('\\', '/', $str);
        }
    }

}
