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
        private $data = [];

        /**
         * @var int
         */
        private $pos = 0;

        /**
         * @param FileInfo $file
         */
        public function add(FileInfo $file) {
            $this->data[] = $file;
        }

        /**
         * @return FileInfo
         * @throws \TheSeer\phpDox\FileInfoCollectionException
         */
        public function current() {
            if (!isset($this->data[$this->pos])) {
                throw new FileInfoCollectionException('Empty collection');
            }
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
