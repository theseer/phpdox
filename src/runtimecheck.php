<?php declare(strict_types = 1);

if (\defined('HHVM_VERSION')) {
    \fwrite(
        \STDERR,
        "\nWARNING - UNSUPPORTED RUNTIME\n\n" .
        "It seems like you are using HHVM to run phpDox.\n" .
        "This version of phpDox has not been tested with HHVM.\n\n" .
        "Please use PHP 7.1+.\n\n"
    );

    return;
}

if ((\version_compare(\phpversion(), '7.1', 'lt'))) {
    \fwrite(
        \STDERR,
        \sprintf(
            'phpDox requires PHP 7.1 or later; ' .
            "Upgrading to the latest version of PHP is highly recommended. (Version used: %s)\n\n",
            \phpversion()
        )
    );

    exit(1);
}
