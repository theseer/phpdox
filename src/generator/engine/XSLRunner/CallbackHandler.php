<?php

namespace Carica\Xsl\Runner;

/**
* Callback for xsl: load an xml document
*
* @param string $url
* @return \DOMDocument
*/
function XsltCallback($class) {
    return call_user_func_array( array('TheSeer\\phpDox\\Engine\\XSLRunner', 'XsltCallback'), func_get_args());
}