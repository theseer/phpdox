<?php

namespace u\v\w {

   interface interfaceB {



   }

   /**
    * Description Title of Class
    *
    * This is the body of the description for the class at hand. It
    * actually spans over multiple lines just to test if the
    * parser will find it and the builder adopts as needed. Let's see
    * if the <node> escaping works & is sound ;)
    *
    * @package Demo
    * @category PHP
    * @since 1.0.0
    * @author Reiner Zufall <reiner@zufall.net>
    * @copyright Reiner Zufall <reiner@zufall.net>, All rights reserved
    * @version 1.0.0
    * @access public
    * @license BSD License
    *
    */
   final class foo extends \bla\blupp\parentclass implements \some\other\interfaceA, interfaceB {

      const ABC = '123';
      const DDD = 1234;

      public $foop = 1.05;
      protected $property = 0;
      private $member = 'A';
      protected $test = "B";
      public $arr = array(1,2,3,array(4));
      public $xx  = array(
         'a' => 1,
         'b' => 'b',
         'c' => 0.5,
         'd' => array('a','b',__FILE__, self::DDD)
      );
      private $xy = null;
      private $xz = VERSION;

      public function bar(someClass $obj, Array $f = null) {
         $this->property = $obj;
         $test = array('abc','def');
         for($x=0; $x<10; $x++) {
            $obj->prop++;
         }
      }

      protected static function baz($a, $b, $c) {
         // nothing yet
      }
   }

   class SecondClas {
      public final function test(Array $x = array('a' => 1, 'b' => 2)) {
      }
   }

}


?>