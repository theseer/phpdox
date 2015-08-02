<?php
namespace some\ns;

class SomeClass
{
    /**
     * @var Callable
     */
    private $c;

    /**
     * @return Callable
     */
    public function getIt(){
        return $this->c;
    }

}
