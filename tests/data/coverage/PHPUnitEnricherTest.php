<?php
use TheSeer\phpDox\Generator\Enricher;
use TheSeer\phpDox\Generator\Enricher\PHPUnitConfig;

class PHPUnitEnricherTest extends \PHPUnit\Framework\TestCase {


    public function testCoverageInformationIsImportedProperly() {

        $config = $this->getMockBuilder(PHPUnitConfig::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $config->expects($this->once())
               ->method('getCoveragePath')
               ->will($this->returnValue(__DIR__ . '/coverage'));

        $enricher = new Enricher\PHPUnit($config);

        $stub = new TheSeer\fDOM\fDOMDocument();
        $stub->preserveWhiteSpace = false;
        $stub->load(__DIR__ . '/xml/classes/Api_Helper_SummaryFactory.xml');
        $stub->registerNamespace('phpdox', 'http://xml.phpdox.net/src');

        $event = new \TheSeer\phpDox\Generator\ClassStartEvent(
            new \TheSeer\phpDox\Generator\ClassObject($stub)
        );
        $enricher->enrichClass($event);

        $stub->formatOutput = true;
        echo $stub->saveXML();
    }
}
