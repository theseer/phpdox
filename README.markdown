phpDox
======

*phpDox* is an alternative project to PHPDocumentor.


Requirements
------------

- PHP Version 5.3.2+
  - ext/dom
  - ext/xsl
  - ext/iconv (version >= 2.12; http://www.gnu.org/software/libiconv/documentation/libiconv/iconv.1.html)
- PHPDepend's staticReflection (https://github.com/manuelpichler/staticReflection)
- fDOMDocument (https://github.com/theseer/fDOMDocument)
- DirectoryScanner (https://github.com/theseer/DirectoryScanner)
- fXSL (https://github.com/theseer/fXSL)
- PHP-Timer (https://github.com/sebastianbergmann/php-timer) 
- ezc / ZetaCompoents ConsoleTools (http://zeta-components.org/)


User Installation
-----------------

phpDox can be installed from pear. If you want to use phpDox and not develop its core, feel
free to directly install it from pear:

    sudo pear channel-discover pear.netpirates.net
    sudo pear channel-discover pear.pdepend.org
    sudo pear channel-discover pear.phpunit.de
    sudo pear channel-discover components.ez.no
    sudo pear install -a theseer/phpDox


This should take care of installing all the required dependencies for you.


Developer Installation
----------------------

In case you want to go bleeding edge or hack on the source, you'll have to clone this repository.

 *NOTE*
    The phpdox.php bootstrap file will assume that all depedencies have been installed from pear. In case
    you do clone all the source repositories yourself, you have to adjust the include paths for them.


To make things work, you now have to manually install the following pear dependencies (or clone their
repositories and then adjust the paths in the phpdox.php bootstrap file):

    sudo pear channel-discover pear.netpirates.net
    sudo pear install theseer/DirectoryScanner
    sudo pear install theseer/fDOMDocument
    sudo pear install theseer/fXSL

phpDox makes heavy use of PDepend's staticReflection, which can either be installed via pear:

    sudo pear channel-discover pear.pdepend.org
    sudo pear install pdepend/staticReflection-beta

In case that doesn't work for you, you may decide to use the staticReflection submodule linked
with phpDox's repository.

In case you do not use PHPUnit, you need to discover its pear channel for a dependency:

    sudo pear channel-discover pear.phpunit.de
    sudo pear install phpunit/php_timer

Finally, you'll need the ezcConsoleTools from components.ez.no:

    sudo pear channel-discover components.ez.no
    sudo pear install ezc/ConsoleTools 


Now you of course still need the source code of phpDox itself.
Get the source code from GIT:

    git clone git://github.com/theseer/phpdox.git

_Note: The following is only required if you did not install staticReflection from pear!_

    git submodule init
    git submodule update


Usage Examples
--------------

You can run phpdox like this:

    ./phpdox.php --help
    
Sample invocation to parse and generate html output:

    ./phpdox.php -x /tmp/xml1 -c ~/Downloads/ZendFramework-1.11.5/library/Zend -d /tmp/docs1 -g html


Trouble Shooting
----------------

* If you run phpdox.php and get the following error:

        PHP Fatal error:  require_once(): Failed opening required 'ezc/Base/base.php' (include_path='.:/usr/share/php:/usr/share/pear') in /var/www/phpdox/phpdox.php on line 58

    Make sure you've installed ezc/ConsoleTools (see requirements).
    Make sure that the ezc/ folder is located in one of the include_path of the error. If not, create a symbolic link.

* If you run phpdox.php and get the following error:

        PHP Warning:  require(TheSeer/DirectoryScanner/autoload.php): failed to open stream: No such file or directory in /var/www/phpdox/phpdox.php on line 44
    
    Make sure you've installed the pear dependencies mentionned in the requirement section.

* If you try to install theseer/fXSL and get the following error:

        theseer/fXSL requires PHP extension "xsl"
        No valid packages found
        install failed
    
    Try to install the xsl extention of PHP. On Ubuntu, you can simply use:

        sudo apt-get install php5-xsl

    Once the extention is installed, you can re-try to install the fXSL dependency.