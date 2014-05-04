phpDox
======

*phpDox* is a documentation generator for generating API documentation in HTML format, for instance, from PHP source code.


Requirements
------------

- PHP Version 5.3.3+
  - ext/dom
  - ext/xsl
  - ext/iconv and [libiconv version >= 2.12](http://www.gnu.org/software/libiconv/documentation/libiconv/iconv.1.html)
- PHPParser [PHP Parser API](https://github.com/nikic/PHP-Parser)
- [fDOMDocument](http://github.com/theseer/fDOMDocument)
- [DirectoryScanner](http://github.com/theseer/DirectoryScanner)
- [fXSL](http://github.com/theseer/fXSL)
- [PHP_Timer](http://github.com/sebastianbergmann/php-timer)
- Zeta Components [ConsoleTools](http://zetac.org/ConsoleTools)


User Installation
-----------------

phpDox is shipping as a selfcontained executable phar archive. You can grab your copy here:

- [Release 0.6.6](http://phpdox.de/releases/phpdox.phar)


    > wget http://phpdox.de/releases/phpdox.phar
    > chmod +x phpdox.phar
    > sudo mv phpdox.phar /usr/bin/phpdox

    > phpdox --version
    phpDox 0.6.6 - Copyright (C) 2010 - 2014 by Arne Blankerts


_Note: Starting with release 0.6.6 the pear package distribution is merely a wrapper for the selfcontained phar._

Developer Installation
----------------------

In case you want to go bleeding edge or hack on the source, you will have to clone this repository.

_Note: The `phpdox.php` bootstrap file assumes that all depedencies have been installed using the PEAR Installer.
In case you do clone the source repository and used composer for the dependency management, you have to use the provided
wrapper in composer/bin/phpdox._

    > git clone git://github.com/theseer/phpdox.git
    > composer install


Usage Examples
--------------

You can run phpDox like this:

    phpdox --help

As of version 0.4 phpDox requires an xml configuration file. In case a project you want to generate documentation for does not come with one, you can create it by calling

    phpdox --skel > phpdox.xml.dist


Sample invocation to parse and generate output based on the default phpdox.xml configuration file

    phpdox



Changelog
---------

#####Release 0.6.6

* Updated Dependencies (DirectoryScanner to 1.3.0, PHPParser to 1.0.0, fDOMDocument to 1.5.0)
* Internal Adjustments for updated Dependencies and API Changes
* Enhanced HTML Output for Parameter lists
* Refactored @inheritdoc support
* Better Windows support (Thanks to Thomas Weinert)
* Added XSD for phpdox.xml config file (Thanks to Thomas Weinert)
* Enhanced PHPUnit enricher to not claim empty classes are untested

#####Older Releases

* Please refer to the git history for now.

