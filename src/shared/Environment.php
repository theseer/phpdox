<?php
namespace TheSeer\phpDox {

    class Environment {

        public function ensureFitness() {
            $this->ensureTimezoneSet();
            $this->ensureRequiredExtensionsLoaded();
            $this->disableXDebug();
        }

        private function ensureRequiredExtensionsLoaded() {
            $required = array('tokenizer', 'iconv', 'fileinfo', 'libxml', 'dom', 'xsl', 'mbstring','pcre');
            $missing = array();

            foreach ($required as $test) {
                if (!extension_loaded($test)) {
                    $missing[] = sprintf('ext/%s not installed/enabled', $test);
                }
            }

            try {
                $test = preg_replace( '/[\x{0430}-\x{04FF}]/iu', '', '-АБВГД-' );
                if ($test != '--') {
                    throw new \ErrorException('PCRE unicode support broken.');
                }
            } catch(\ErrorException $e) {
                $missing[] = 'PCRE installation does not support unicode / unicode properties.';
            }

            if (count($missing)) {
                throw new EnvironmentException(
                    join("\n", $missing),
                    EnvironmentException::ExtensionMissing
                );
            }
        }

        private function disableXDebug() {
            if (!extension_loaded('xdebug')) {
                return;
            }
            ini_set('xdebug.scream', 0);
            ini_set('xdebug.max_nesting_level', 8192);
            ini_set('xdebug.show_exception_trace', 0);
            xdebug_disable();
        }

        private function ensureTimezoneSet() {
            if (!ini_get('date.timezone')) {
                date_default_timezone_set('UTC');
            }
        }
    }

}
