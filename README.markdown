phpdox
======

**phpdox** is a documentation generator for generating API documentation from PHP source code.


Requirements
------------

* PHP 5.3 with the [iconv](http://php.net/iconv) extension (version 2.12 or later of the iconv library is required)


Installation
------------

phpdox should be installed using the PEAR Installer, the backbone of the [PHP Extension and Application Repository](http://pear.php.net/) that provides a distribution system for PHP packages.

Depending on your OS distribution and/or your PHP environment, you may need to install PEAR or update your existing PEAR installation before you can proceed with the following instructions. `sudo pear upgrade PEAR` usually suffices to upgrade an existing PEAR installation. The [PEAR Manual ](http://pear.php.net/manual/en/installation.getting.php) explains how to perform a fresh installation of PEAR.

The following two commands are all that is required to install PHPUnit using the PEAR Installer:

    sudo pear config-set auto_discover 1
    sudo pear install pear.netpirates.net/phpdox

The following dependencies need to be installed manually in case you want to use phpdox from a Git checkout:

    sudo pear channel-discover pear.netpirates.net
    sudo pear install theseer/DirectoryScanner
    sudo pear install theseer/fDOMDocument
    sudo pear install theseer/fXSL

    sudo pear channel-discover pear.pdepend.org
    sudo pear install pdepend/staticReflection-beta

    sudo pear channel-discover pear.phpunit.de
    sudo pear install phpunit/PHP_Timer

    sudo pear channel-discover components.ez.no
    sudo pear install ezc/ConsoleTools

Usage Examples
--------------

You can run phpdox like this:

    ./phpdox.php --help

Sample invocation to parse and generate html output:

    ./phpdox.php -x /tmp/xml1 -c ~/Downloads/ZendFramework-1.11.5/library/Zend -d /tmp/docs1 -g html


Troubleshooting
---------------

* If you run `phpdox.php` and get an error like `PHP Fatal error:  require_once(): Failed opening required 'ezc/Base/base.php' (include_path='.:/usr/share/php:/usr/share/pear') in /var/www/phpdox/phpdox.php on line 58` make sure that you have installed `ezc/ConsoleTools` (see Installation) and that the `ezc` folder is located in your `include_path`.

* If you run `phpdox.php` and get an error like `PHP Warning:  require(TheSeer/DirectoryScanner/autoload.php): failed to open stream: No such file or directory in /var/www/phpdox/phpdox.php on line 44` make sure you have installed the dependencies mentioned in the Installation section.

* If you try to install `theseer/fXSL` and get an error like `theseer/fXSL requires PHP extension "xsl"` try to install the `xsl` extention of PHP. On Ubuntu, you can simply use `sudo apt-get install php5-xsl`. Once the extension is installed, you can re-try to install the fXSL dependency.
