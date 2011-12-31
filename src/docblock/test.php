<?php

 $lines = array(
    '@annotation(value here)',
    '@annotation value here',
    '@Id @Column(type="integer")',
    '@FLOW3\Pointcut("method(.*->delete.*())")'
 );

 foreach($lines as $line) {
    $rc = preg_match('/^\@([a-zA-Z0-9_]+)(.*)$/', $line, $lineParts);
    echo "\n\n$line:\n";
    var_dump($lineParts);    
 }
