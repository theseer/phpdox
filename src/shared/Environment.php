<?php
namespace TheSeer\phpDox {

    class Environment {

        public function ensureFitness() {
            $this->ensureRequiredExtensionsLoaded();
            $this->disableXDebug();
            $this->ensureTimezoneSet();
        }

        private function ensureRequiredExtensionsLoaded() {
            $required = array('tokenizer', 'iconv', 'fileinfo', 'libxml', 'dom', 'xsl', 'mbstring');
            $missing = array();

            foreach ($required as $test) {
                if (!extension_loaded($test)) {
                    $missing[] = sprintf('ext/%s not installed/enabled', $test);
                }
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
                ini_set('date.timezone', 'UTC');
            }
        }
    }

}
