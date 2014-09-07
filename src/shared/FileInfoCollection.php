<?php
namespace TheSeer\phpDox {

    /**
     * Class FileInfoCollection
     *
     * @package TheSeer\phpDox
     */
    class FileInfoCollection implements \Iterator, \Countable {

        /**
         * @var FileInfo[]
         */
        private $data;

        /**
         * @var int
         */
        private $pos;

        /**
         * @param FileInfo $file
         */
        public function add(FileInfo $file) {
            $this->data[] = $file;
        }

        /**
         * @return FileInfo
         */
        public function current() {
            return $this->data[$this->pos];
        }

        /**
         *
         */
        public function next() {
            $this->pos++;
        }

        /**
         * @return int
         */
        public function key() {
            return $this->pos;
        }

        /**
         * @return bool
         */
        public function valid() {
            return $this->count() > $this->pos;
        }

        /**
         *
         */
        public function rewind() {
            $this->pos = 0;
        }

        /**
         * @return int
         */
        public function count() {
            return count($this->data);
        }

    }

}
