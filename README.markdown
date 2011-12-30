phpDox
======

*phpDox* is a documentation generator for generating API documentation in HTML format, for instance, from PHP source code.


Requirements
------------

- PHP Version 5.3.2+
  - ext/dom
  - ext/xsl
  - ext/iconv and [libiconv version >= 2.12](http://www.gnu.org/software/libiconv/documentation/libiconv/iconv.1.html)
- PHP_Depend's [Static Reflection API](http://github.com/manuelpichler/staticReflection)
- [fDOMDocument](http://github.com/theseer/fDOMDocument)
- [DirectoryScanner](http://github.com/theseer/DirectoryScanner)
- [fXSL](http://github.com/theseer/fXSL)
- [PHP_Timer](http://github.com/sebastianbergmann/php-timer)
- Zeta Components [ConsoleTools](http://zetac.org/ConsoleTools)


User Installation
-----------------

phpDox should be installed using the PEAR Installer, the backbone of the [PHP Extension and Application Repository](http://pear.php.net/) that provides a distribution system for PHP packages.

Depending on your OS distribution and/or your PHP environment, you may need to install PEAR or update your existing PEAR installation before you can proceed with the following instructions. `sudo pear upgrade PEAR` usually suffices to upgrade an existing PEAR installation. The [PEAR Manual ](http://pear.php.net/manual/en/installation.getting.php) explains how to perform a fresh installation of PEAR.

The following two commands (which you may have to run as `root`) are all that is required to install phpDox using the PEAR Installer:

    sudo pear config-set auto_discover 1
    sudo pear install pear.netpirates.net/phpDox

This should take care of installing all the required dependencies for you.


Developer Installation
----------------------

In case you want to go bleeding edge or hack on the source, you will have to clone this repository.

_Note: The `phpdox.php` bootstrap file assumes that all depedencies have been installed using the PEAR Installer. In case you do clone all the source repositories yourself, you have to adjust the include paths for them._

To make things work, you now have to manually install the following dependencies using the PEAR Installer (or clone their repositories and then adjust the paths in the `phpdox.php` bootstrap file):

    sudo pear install theseer/DirectoryScanner
    sudo pear install theseer/fDOMDocument
    sudo pear install theseer/fXSL
    sudo pear install pdepend/staticReflection-beta
    sudo pear install phpunit/PHP_Timer
    sudo pear install ezc/ConsoleTools

Now you are ready to check out the source code of phpDox from its Git repository:

    git clone git://github.com/theseer/phpdox.git


Usage Examples
--------------

You can run phpDox like this:

    ./phpdox.php --help

Sample invocation to parse and generate HTML output:

    ./phpdox.php -x /tmp/xml1 -c ~/Downloads/ZendFramework-1.11.5/library/Zend -d /tmp/docs1 -g html


Trouble Shooting
----------------

* If you run `phpdox.php` and get the following error:

        PHP Fatal error:  require_once(): Failed opening required 'ezc/Base/base.php' (include_path='.:/usr/share/php:/usr/share/pear') in /var/www/phpdox/phpdox.php on line 58

    Make sure you have installed ezc/ConsoleTools (see above).
    Make sure that the `ezc/` folder is located in one of the `include_path? of the error. If not, create a symbolic link.

* If you run `phpdox.php` and get the following error:

        PHP Warning:  require(TheSeer/DirectoryScanner/autoload.php): failed to open stream: No such file or directory in /var/www/phpdox/phpdox.php on line 44

    Make sure you have installed all the dependencies mentioned above.

* If you try to install `theseer/fXSL` and get the following error:

        theseer/fXSL requires PHP extension "xsl"
        No valid packages found
        install failed

    Try to install the xsl extention of PHP. On Ubuntu, you can simply use:

        sudo apt-get install php5-xsl

    Once the extension is installed, you can retry to install the fXSL package.
