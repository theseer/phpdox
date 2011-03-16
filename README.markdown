phpdox
======

*PHPDox* is an alternative projet to PHPDocumentor.


Requirements
------------
In order to use phpdox, you'll need the ezcConsoleTools from components.ez.no:
sudo pear channel-discover components.ez.no
sudo pear install ezc/ConsoleTools 

You also need to make sure you're using PHP 5.3+.

Installation
------------
Get the source code from GIT:
    ~ $ cd /var/www/
    /var/www/ $ git clone --recursive git@github.com:Username/phpdox.git

_Note: If you've forgot the --recursive argument, you'll not have the submodule in lib/. So you might want to use:_
    /var/www/ $ git submodule init
    /var/www/ $ git submodule update


Usage Examples
--------------
You can run phpdox like this:
    /var/www/ $ phpdox/phpdox.php


trouble shooting
----------------

* If you run phpdox.php and get the following error:
    PHP Fatal error:  require_once(): Failed opening required 'ezc/Base/base.php' (include_path='.:/usr/share/php:/usr/share/pear') in /var/www/phpdox/phpdox.php on line 58

Make sure you've installed ezc/ConsoleTools (see requirements).
Make sure that the ezc/ folder is located in one of the include_path of the error. If not, create a symbolic link.


* If you run phpdox.php and get the following error:
    PHP Fatal error:  Uncaught exception 'LogicException' with message 'Passed array does not specify an existing static method (class '\ezcBase' not found)' in /var/www/phpdox/phpdox.php:65

Make sure you're using PHP 5.3+.
If your setup is ok, you might want to try to edit the line 65 of the phpdox.php file to remove the \.
Change this line:
spl_autoload_register( array('\ezcBase','autoload'));
To this:
spl_autoload_register( array('ezcBase','autoload'));

