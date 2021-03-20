<?php
if (PHP_VERSION_ID < 80000) {
    class_alias('TheSeer\fXSL\fXSLTProcessorOld', 'TheSeer\fXSL\fXSLTProcessor');
} else {
    class_alias('TheSeer\fXSL\fXSLTProcessorNew', 'TheSeer\fXSL\fXSLTProcessor');
}
