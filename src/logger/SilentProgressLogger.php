<?php declare(strict_types = 1);
namespace TheSeer\phpDox;

/**
 * Silent progress logger
 */
class SilentProgressLogger implements ProgressLogger {
    public function progress($state): void {
    }

    public function reset(): void {
    }

    public function completed(): void {
    }

    public function log($msg): void {
    }

    public function buildSummary(): void {
    }
}
