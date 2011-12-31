<?php
/**
* Xslt Callback object. Take a PHP type string from an annotation and create elements from it.
*
* @license http://www.opensource.org/licenses/mit-license.php The MIT License
* @copyright Copyright (c) 2011 Thomas Weinert
*
* @package XslRunner
*/

namespace Carica\Xsl\Runner\Callback;

use \Carica\Xsl\Runner as Runner;

/**
* Xslt Callback object. Take a PHP type string from an annotation and create elements from it.
*
* @package XslRunner
*/
class TypeString implements Runner\Callback  {

  private $_splitter = '(([()\s|=><.,]+))';

  /**
  * Create a new DOMDocument, load the given xml file and return the document.
  *
  * @param string $typeString
  * @return DOMDocument
  */
  public function __invoke($typeString) {
    $dom = new \DOMDocument();
    $dom->appendChild($root = $dom->createElement('variable-type'));
    $matches = array();
    if ($matches = preg_split($this->_splitter, $typeString, -1, PREG_SPLIT_DELIM_CAPTURE)) {
      foreach ($matches as $match) {
        if (preg_match($this->_splitter, $match)) {
          $element = $dom->createElement('text');
        } else {
          $element = $dom->createElement('type');
        }
        $element->appendChild($dom->createTextNode($match));
        $root->appendChild($element);
      }
    }
    return $dom;
  }
}