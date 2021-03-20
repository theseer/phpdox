<?php

namespace TheSeer\fXSL;

use PHPUnit\Framework\TestCase;

class fxsltprocessorTest extends TestCase {

    public function testLoadingStylesheetFromFile() {
        $dom = new \DOMDocument();

        $xsl = new fXSLTProcessor();
        $xsl->loadStylesheetFromFile(__DIR__ . '/_data/basic.xsl');

        $res = $xsl->transformToDoc($dom);
        $this->assertEquals('test', $res->documentElement->nodeName);
    }

    public function testLoadingStylesheetFromXML() {
        $dom = new \DOMDocument();

        $xsl = new fXSLTProcessor();
        $xsl->loadStylesheetFromXML( file_get_contents(__DIR__ . '/_data/basic.xsl'));

        $res = $xsl->transformToDoc($dom);
        $this->assertEquals('test', $res->documentElement->nodeName);
    }

    public function testLoadingInvalidStylesheetFromFileThrowsException() {
        $xsl = new fXSLTProcessor();

        $this->expectException('\TheSeer\fXSL\fXSLTProcessorException');
        $xsl->loadStylesheetFromFile(__DIR__ . '/_data/broken.xsl');
    }

    public function testTransform() {
        $tpl = new \DOMDocument();
        $tpl->load(__DIR__ . '/_data/test.xsl');

        $dom = new \DOMDocument();

        $xsl = new fXSLTProcessor($tpl);
        $xsl->registerPHPFunctions("TheSeer\\fXSL\\fxsltprocessorTest::demo");

        $test = new fXSLCallback('test:only', 'test');
        $test->setObject($this);

        $xsl->registerCallback($test);

        $result = $xsl->transformToXml($dom);

        $testDoc = new \DOMDocument();
        $testDoc->loadXML($result);
        $testXPath = new \DOMXPath($testDoc);

        $demos = $testXPath->evaluate("/root/demo");
        /* @var $demos \DOMNodeList */
        $this->assertEquals(1, $demos->length);
        $this->assertEquals(
            self::demo(),
            $demos->item(0)->textContent
        );

        $roots = $testXPath->evaluate("/root/test/root");
        /* @var $roots \DOMNodeList */
        $this->assertEquals(1, $roots->length);

        $this->assertEquals(
            $this->bar('hallo', 'welt')->textContent,
            $roots->item(0)->textContent
        );
    }

    public static function demo() {
        return 'Demo reply';
    }

    public function bar($a, $b) {
        $x = new \DOMDocument();
        $x->loadXML('<?xml version="1.0" ?><root />');
        $p = $x->createTextNode($a . ' -> ' . $b);
        $x->documentElement->appendChild($p);
        return $x->documentElement;
    }

}
