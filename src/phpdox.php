<?php

namespace TheSeer\phpDox {

   require 'autoload.php';

   $dom = new \DOMDocument('1.0', 'UTF-8');
   $root = $dom->createElementNS('http://phpdox.de/xml#', 'phpdox');
   $dom->appendChild($root);

   $factory = new stackHandlerFactory($dom);
   $parser  = new parser($factory, $root);

   //$parser->parseFile('../test0.php');
   $parser->parseFile('../test4.php');

   echo $dom->saveXML();

}
