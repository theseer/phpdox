<?php
namespace data\issue170\src {

    /**
     * @method string getSomeString()
     * @method void setValue(integer $integer)
     * @method setString(integer $integer)
     * @method Sample getSelf($x = NULL) This method returns an instance of Sample
     * @method static \Exception|\OtherException getException(\Exception $chain = NULL) Descriptive Text
     * @method A|B|C getMagic($p1, \SplFileInfo $p2, $s = 'fix string', $x = NULL) Hell world
     */
    class Sample {

        /**
         * Generic magic call helper
         *
         * @param $name
         * @param $args
         */
        public function __call($name, $args) {
            // ...
        }

    }

}
