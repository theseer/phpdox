<?php

class foo {

   /** @some annotation */
   protected $data;
   
   /** @route("/") */
   public function test() {}

   /**
    * @other annotation
    */
   public function foobar() {}


/**
* Retrieve network usage ...
*
* @param string $cloudStackSetup
* @param date $fromDate start date
* @param date $toDate end date
*
* @return Array
* @todo refactor returned data - it is not HTML
* @Secure(roles="ROLE")
*/
   public function baz($cloudStacksetup, date $fromDate, date $toDate){}

}
