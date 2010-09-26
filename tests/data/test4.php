<?php

namespace u\v\w {

   interface interfaceB {



   }

   final class foo extends \bla\blupp\parentclass implements \some\other\interfaceA, interfaceB {

      const ABC = '123';
      const DDD = 1234;

      public $foop = 1.05;
      protected $property = 0;
      private $member = 'A';
      public $arr = array(1,2,3,4);

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
      public final function test() {
      }
   }

}


?>