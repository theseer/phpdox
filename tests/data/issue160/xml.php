<?php

 /**
  * Pipe the output of this file to xmllint:
  *
  * e.g.  php xml.php | xmllint -
  */

 $x = chr(0xA0) . chr(0x7B) . chr(0x40) . chr(0x69);
 echo '<?xml version="1.0" encoding="utf-8" ?><r>' . $x . '</r>';


