<?php

/**
 * Class Php72ObjectTypeClass
 *
 * @method object getObject()
 * @method \IteratorIterator getClassObject()
 *
 * @property object $magicObject
 * @property \SplStack $magicClassObject
 */
class Php72ObjectTypeClass {

    /**
     * @var object
     */
    public $object;

    /**
     * @var \ArrayObject
     */
    public $class;

    function objectReturn(): object {
        return new stdClass();
    }

    function objectParam(object $param) {}

    /**
     * @param object $param
     */
    function objectParamPhpDoc($param) {}

    public function classParam(SplFileInfo $file) {}

    /**
     * @param \SplFileInfo $file
     */
    public function classParamPhpDoc($file) {}

    /**
     * @return object
     */
    public function objectReturnPhpDoc() {
        return new stdClass();
    }

    function stringReturn(): string {
        return '';
    }

    /**
     * @return string
     */
    function stringReturnPhpDoc() {
        return '';
    }

    function classReturn(): DateTime {
        return new DateTime();
    }

    /**
     * @return \DateTime
     */
    function classReturnPhpDoc() {
        return new DateTime();
    }
}
