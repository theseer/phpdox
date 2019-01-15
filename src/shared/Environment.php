<?php declare(strict_types = 1);
namespace TheSeer\phpDox;

class Environment {
    public function ensureFitness(): void {
        $this->ensureTimezoneSet();
        $this->ensureRequiredExtensionsLoaded();
        $this->disableXDebug();
    }

    private function ensureRequiredExtensionsLoaded(): void {
        $required = ['tokenizer', 'iconv', 'fileinfo', 'libxml', 'dom', 'xsl', 'mbstring', 'pcre'];
        $missing  = [];

        foreach ($required as $test) {
            if (!\extension_loaded($test)) {
                $missing[] = \sprintf('ext/%s not installed/enabled', $test);
            }
        }

        try {
            $test = \preg_replace('/[\x{0430}-\x{04FF}]/iu', '', '-АБВГД-');

            if ($test != '--') {
                throw new \ErrorException('PCRE unicode support broken.');
            }
        } catch (\ErrorException $e) {
            $missing[] = 'PCRE installation does not support unicode / unicode properties.';
        }

        if (\count($missing)) {
            throw new EnvironmentException(
                \implode("\n", $missing),
                EnvironmentException::ExtensionMissing
            );
        }
    }

    private function disableXDebug(): void {
        if (!\extension_loaded('xdebug')) {
            return;
        }
        \ini_set('xdebug.scream', 'off');
        \ini_set('xdebug.max_nesting_level', '8192');
        \ini_set('xdebug.show_exception_trace', 'off');
        xdebug_disable();
    }

    private function ensureTimezoneSet(): void {
        if (!\ini_get('date.timezone')) {
            \date_default_timezone_set('UTC');
        }
    }
}
