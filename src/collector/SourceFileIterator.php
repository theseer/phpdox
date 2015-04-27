<?php
namespace TheSeer\phpDox\Collector {

    use TheSeer\phpDox\FileInfo;

    class SourceFileIterator implements \Iterator {

        private $iterator;
        private $srcDir;
        private $encoding;

        /**
         * @param \Iterator $iterator
         * @param FileInfo  $srcDir
         * @param string    $encoding
         */
        public function __construct(\Iterator $iterator, FileInfo $srcDir, $encoding) {
            $this->iterator = $iterator;
            $this->srcDir = $srcDir;
            $this->encoding = $encoding;
        }

        /**
         * @return SourceFile
         */
        public function current() {
            return new SourceFile($this->iterator->current()->getPathname(), $this->srcDir, $this->encoding);
        }

        /**
         * (PHP 5 &gt;= 5.0.0)<br/>
         * Move forward to next element
         *
         * @link http://php.net/manual/en/iterator.next.php
         * @return void Any returned value is ignored.
         */
        public function next() {
            $this->iterator->next();
        }

        /**
         * (PHP 5 &gt;= 5.0.0)<br/>
         * Return the key of the current element
         *
         * @link http://php.net/manual/en/iterator.key.php
         * @return mixed scalar on success, or null on failure.
         */
        public function key() {
            return $this->iterator->key();
        }

        /**
         * (PHP 5 &gt;= 5.0.0)<br/>
         * Checks if current position is valid
         *
         * @link http://php.net/manual/en/iterator.valid.php
         * @return boolean The return value will be casted to boolean and then evaluated.
         *       Returns true on success or false on failure.
         */
        public function valid() {
            return $this->iterator->valid();
        }

        /**
         * (PHP 5 &gt;= 5.0.0)<br/>
         * Rewind the Iterator to the first element
         *
         * @link http://php.net/manual/en/iterator.rewind.php
         * @return void Any returned value is ignored.
         */
        public function rewind() {
            $this->iterator->rewind();
        }

    }

}
