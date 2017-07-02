phpDox
======

*phpDox* is a documentation generator for PHP projects.
This includes, but is not limited to, API documentation. The main focus is on enriching
the generated documentation with additional details like code coverage, complexity information
and more.

[![Build Status](https://travis-ci.org/theseer/phpdox.svg?branch=master)](https://travis-ci.org/theseer/phpdox)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/theseer/phpdox/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/theseer/phpdox/?branch=master)

Requirements
------------

- PHP Version 5.5+ (PHP 7.x recommended)
  - ext/dom
  - ext/xsl
  - ext/iconv and [libiconv version >= 2.12](http://www.gnu.org/software/libiconv/documentation/libiconv/iconv.1.html)
- PHPParser [PHP Parser API](https://github.com/nikic/PHP-Parser)
- [fDOMDocument](http://github.com/theseer/fDOMDocument)
- [DirectoryScanner](http://github.com/theseer/DirectoryScanner)
- [fXSL](http://github.com/theseer/fXSL)
- [PHP_Timer](http://github.com/sebastianbergmann/php-timer)


Phar Installation
-----------------

phpDox is shipping as a selfcontained executable phar archive. You can grab your copy from the
[releases](https://github.com/theseer/phpdox/releases/latest) section or install using [phive](https://phar.io):

    phive install phpdox

You can now execute phpdox on the command line:

    tools/phpdox --version

If everything worked out, you should get an output like this:

    phpDox 0.10.1 - Copyright (C) 2010 - 2017 by Arne Blankerts and Contributors

_Note: Some Linux distributions ship PHP with ext/suhosin and disabled phar execution. To make use of phpDox in such an environment, you need to enable phar execution by adding phar to the executor white list: suhosin.executor.include.whitelist="phar"_


Composer Installation
---------------------

Additionally, phpDox can be installed via composer:

    composer require --dev theseer/phpdox 

You can now execute phpdox on the command line:

    vendor/bin/phpdox --version

If everything worked out, you should get an output like this:

    phpDox 0.10.1 - Copyright (C) 2010 - 2017 by Arne Blankerts and Contributors


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
