<?php

namespace TheSeer\fXSL;

use PHPUnit\Framework\TestCase;

class fXSLCallbackTest extends TestCase {

    public function testSimple() {
        $object = new \stdClass();

        $callback = new fXSLCallback("test:only");

        $this->assertEquals("test:only", $callback->getNamespace());

        $callback->setObject($object);
        $this->assertEquals($object, $callback->getObject());
    }

}
