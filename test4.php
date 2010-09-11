<?php

namespace u\v\w {

   final class foo extends \bla\blupp\parentclass implements \some\other\interfaceA, interfaceB {

      const ABC = '123';
      const DDD = 1234;

      protected $property;

      public function bar(someClass $obj) {
         $this->property = $obj;
      }

      protected static function baz($a, $b, $c) {
         // nothing yet
      }
   }

}


?>