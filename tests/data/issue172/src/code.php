<?php


namespace Foo\Bar {

    trait BarTrait {
        public function fooA() {}
    }

}

namespace TheSeer\Test\Fixtures {

    use Foo\Bar;

    trait A {
        public function doA() { }
    }

    trait B {
        public function doB() { }
        public function doA() { }
    }

    trait C {
        public function doC() { }
        public function doA() { }
    }

    class X {

        use \Foo\Bar\BarTrait;
        use A, B, C {
            A::doA insteadOf B;
            A::doA insteadof C;
            C::doA as newDoAFromC;
            B::doA as private newDoA;
            C::doC as newDoC;
            Bar\BarTrait::fooA as otherA;
        }

    }

    $x = new X();
    var_dump(get_class_methods($x));

}



