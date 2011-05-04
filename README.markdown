phpdox
======

*PHPDox* is an alternative project to PHPDocumentor.


Requirements
------------

You need to make sure you're using PHP 5.3+.

Also, you'll need to install the following pear dependencies:

    sudo pear channel-discover pear.netpirates.net
    sudo pear install theseer/DirectoryScanner
    sudo pear install theseer/fDOMDocument
    sudo pear install theseer/fXSL

In case you do not use PHPUnit, you need to discover the pear channel for a dependency:

    sudo pear channel-discover pear.phpunit.de
    sudo pear install phpunit/php_timer

Finally, you'll need the ezcConsoleTools from components.ez.no:

    sudo pear channel-discover components.ez.no
    sudo pear install ezc/ConsoleTools 


Installation
------------

Make sure you've installed all the requirements.

Get the source code from GIT:

    git clone --recursive git://github.com/theseer/phpdox.git

_Note: If you've forgot the `--recursive` argument, you'll not have the staticReflection submodule in lib/. So you might want to use:_

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