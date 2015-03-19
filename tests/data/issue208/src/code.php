<?php

const X='def';
const Y='ghi';

class Issue {
    const FooTrue = true;
    const FooFalse = false;
    const FooString = 'abc';
    const FooInt = 123;
    const FooX = \X;
    const FooY = Y;
    const FooMagic = __FILE__;

    private $fooTrue = true;
    private $fooFalse = false;
    private $fooString = 'abc';
    private $fooInt = 123;
    private $fooX = \X;
    private $fooY = Y;
    private $fooMagic = __FILE__;

    public function foo($a = TRUE, $b = FALSE, $c = \X, $d = Y, $e = __FILE__) {}
}
