<?php declare(strict_types=1);

trait fooA {
    public function barA() {
    	echo 'Hello trait A';
    }
}

trait fooB {
    public function barB() {
        echo 'Hello trait B';
    }
}

class test {

    use fooB, fooA {
        barB as demo;
    }
}
