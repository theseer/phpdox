<?php
namespace TheSeer\phpDox {

    /**
     * Generic progress logger
     */
    interface ProgressLogger {

        /**
         * @param $state
         *
         * @throws ProgressLoggerException
         */
        public function progress($state);

        /**
         *
         */
        public function reset();

        /**
         *
         */
        public function completed();

        /**
         * @param $msg
         */
        public function log($msg);

        /**
         *
         */
        public function buildSummary();

    }
}
