<?php declare(strict_types = 1);
namespace TheSeer\phpDox;

class EnvironmentException extends \Exception {
    public const ExtensionMissing = 1;

    public const VendorMissing = 2;
}
