<?php

if (defined('HHVM_VERSION')) {
    fwrite(
        STDERR,
        "It seems like you are using HHVM to run phpDox.\nHHVM is no longer a supported environment." .
             "Please consider using PHP 7.x.\n\n"
    );
    return;
}

if ((version_compare(phpversion(), '5.5', 'lt'))) {
    fwrite(
        STDERR,
        sprintf(
            "phpDox requires PHP 5.5 or later; " .
            "Upgrading to the latest version of PHP is highly recommended. (Version used: %s)\n\n",
            phpversion()
        )
    );

    die(1);
}

if ((version_compare(phpversion(), '7.0', 'lt'))) {
    fwrite(
        STDERR,
        sprintf(
            "You are using an outdated version of PHP (%s) to run phpDox. Please consider upgrading to PHP 7.x!\n\n",
            phpversion()
        )
    );
}
