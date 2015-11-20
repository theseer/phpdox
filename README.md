phpDox
======

*phpDox* is a documentation generator for PHP projects.
This includes, but is not limited to, API documentation.

[![Build Status](https://travis-ci.org/theseer/phpdox.svg?branch=master)](https://travis-ci.org/theseer/phpdox)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/theseer/phpdox/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/theseer/phpdox/?branch=master)

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


User Installation
-----------------

phpDox is shipping as a selfcontained executable phar archive. You can grab your copy from the
[releases](https://github.com/theseer/phpdox/releases/latest) section or directly here:

- [Release 0.8.1](https://github.com/theseer/phpdox/releases/download/0.8.1/phpdox-0.8.1.phar) [734k - [gpg](https://github.com/theseer/phpdox/releases/download/0.8.0/phpdox-0.8.1.phar.asc)]

Installation is simple:

    wget https://github.com/theseer/phpdox/releases/download/0.8.0/phpdox-0.8.0.phar
    chmod +x phpdox-0.8.0.phar
    sudo mv phpdox-0.8.0.phar /usr/bin/phpdox

You can now execute phpdox on the command line:

    phpdox --version

If everything worked out, you should get an output like this:

    phpDox 0.8.0 - Copyright (C) 2010 - 2015 by Arne Blankerts


_Note: Starting with release 0.6.6 the pear package distribution is merely a wrapper for the selfcontained phar._

_Note: Some Linux distributions ship PHP with ext/suhosin and disabled phar execution. To make use of phpDox in such an environment, you need to enable phar execution by adding phar to the executor white list: suhosin.executor.include.whitelist="phar"_

Developer Installation
----------------------

In case you want to go bleeding edge or hack on the source, you will have to clone this repository.

    git clone git://github.com/theseer/phpdox.git
    composer install


Usage Examples
--------------

You can run phpDox like this:

    phpdox --help

As of version 0.4 phpDox requires an xml configuration file. In case a project you want to generate documentation for does not come with one, you can create it by calling

    phpdox --skel > phpdox.xml.dist


Sample invocation to parse and generate output based on the default phpdox.xml configuration file

    phpdox

or you can tell `phpdox` what configuration file to use by calling switch `--file` or in short

    phpdox -f path/to/phpdox.xml
