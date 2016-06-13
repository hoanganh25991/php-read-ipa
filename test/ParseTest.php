<?php
define("IPA_FILE", __DIR__ . "/hoipos.ipa");
use PHPUnit\Framework\TestCase;
use Redoc\IpaParser;

class ParseTest extends TestCase{
    public function testParse(){
        $parser = new IpaParser(IPA_FILE);
        $info = $parser->getBasicInfo();

        $this->assertEquals($info["CFBundleInfoDictionaryVersion"], "6.0");
        $this->assertEquals($info["DTPlatformVersion"], "9.3");
        $this->assertEquals($info["CFBundleName"], "SG TrafficLeh");
        $this->assertEquals($info["iconPath"], "ipa-info/231c14f64f3e41d682d6bfd4f39ccaaa/app-icon.png");
    }
}