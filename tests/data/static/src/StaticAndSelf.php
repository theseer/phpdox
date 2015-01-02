<?php
class StaticAndSelf {

    /**
     * @var self
     */
    private $x;

    /**
     * @return static
     */
    public function foo() {
        return new static();
    }
}
