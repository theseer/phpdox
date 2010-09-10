<?php

namespace u\v\w {

  class foo extends f implements x, y {

     const ABC = '123';

     protected $foo;

     public function bar(someClass $obj) {
          $this->foo = $obj;
     }

     protected static function baz($a, $b, $c) {
        // nothing yet
     }
  }

}
?>