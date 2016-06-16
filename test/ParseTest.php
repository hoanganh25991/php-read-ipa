<?php
define("IPA_FILE", __DIR__ . "/hoipos.ipa");
use PHPUnit\Framework\TestCase;
use Redoc\IpaParser;

class ParseTest extends TestCase{
    public function testParse(){
        $parser = new IpaParser(IPA_FILE);
        $info = $parser->getBasicInfo();

        $this->assertEquals($info[IpaParser::APP_NAME], "SG TrafficLeh");
        $this->assertEquals($info[IpaParser::BUNDLE_INDENTIFIER], "us.originally.trafficleh");
        $this->assertEquals($info[IpaParser::VERSION_NAME], "0.4");
        $this->assertEquals($info[IpaParser::VERSION_CODE], "4");
        $this->assertEquals($info[IpaParser::ICON_PATH], "ipa-info/231c14f64f3e41d682d6bfd4f39ccaaa/app-icon.png");
    }
}