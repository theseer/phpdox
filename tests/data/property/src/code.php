<?php

namespace foo;

const X='abc';

class Code {

    private $a = 123;

    private $b = X;

    protected $c = 'STR';

    public $d = array();

    public $e = null;

    public $f = true;

    private $g = 0.5;

    private $h = 0x1337;

    public function test(Array $a = array(), $b = X, \StdClass $x, Code $y, $z = true) {}

}
