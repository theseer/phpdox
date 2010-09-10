<?php

namespace TheSeer\phpDox {

   require 'parser.php';
   require 'stackhandler.php';

   $dom = new \DOMDocument('1.0', 'UTF-8');
   $root = $dom->createElementNS('http://phpdox.de/xml#', 'phpdox');
   $dom->appendChild($root);

   $factory = new stackHandlerFactory();

   $parser  = new parser($factory, $root);

   $parser->parseFile('../test4.php');

   echo $dom->saveXML();

}