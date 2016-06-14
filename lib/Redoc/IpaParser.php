<?php
namespace Redoc;
define("I", "/");

require_once(__DIR__ . "/Icon.php");
require_once(__DIR__ . "/ZipException.php");
require_once(__DIR__ . "/PPngUncrush.php");

use CFPropertyList\CFPropertyList;
use Exception;

class IpaParser{
    const UN_HANDLE = "sorry, we still not handle this situation";
    const ZIP = "7z";
    const MV = "mv";
    const RM = "rm";
    const PLIST = "Info.plist";
    const OUTPUT_DIR = "ipa-info";
    const OUTPUT_TEMP = "ipa-tmp";
    const APP_ICON = "app-icon.png";
    const VERSION_NAME = "versionName";
    const VERSION_CODE = "versionCode";
    const PACKAGE_NAME = "packageName";
    const MIN_SDK = "minSDK";
    const MIN_SDK_LEVEL = "minSDKLevel";
    const TARGET_SDK = "targetSDK";
    const DATE = "date";
    const ICON_PATH = "iconPath";


    private $ipaFilePath;
    private $outputDir;
    private $options;
    /** @var  Icon */
    private $icon;
    /** @var  CFPropertyList */
    private $plist;
    private $extractFolder;
    private $parsed;

    public function __construct($ipaFilePath, $outputDir = "", $options = array()){
        //check ipa file exist
        if(!file_exists($ipaFilePath)){
            $errMsg = sprintf("ipa file not exist\nfile path: %s", $ipaFilePath);
            throw new Exception(nl2br($errMsg));
        }
        $this->ipaFilePath = $ipaFilePath;

        //check output is directory
        $this->outputDir = self::OUTPUT_DIR;
        if(!empty($outputDir)){
            $this->outputDir = $this->removeSpace($outputDir);
        }

        if(!is_dir($this->outputDir) && !file_exists($this->outputDir)){
            mkdir($this->outputDir, 777, true);
        }

        $this->options = $options;

        $this->parsed = false;

        $md5Input = $this->ipaFilePath + filemtime($this->ipaFilePath);

        $this->extractFolder = $this->outputDir . I . md5($md5Input);

        $this->parse();
    }


    private function parse(){
        $this->checkParse();

        $iconPath = $this->extractFolder . I . self::APP_ICON;

        $plistPath = $this->extractFolder . I . self::PLIST;

        if($this->parsed){
            $msg = "";
            if(!file_exists($this->extractFolder . I . self::APP_ICON)){
                $msg = "Parsed file ipa! But app-icon.png no longer exist";
            }

            if(!file_exists($this->extractFolder . I . self::PLIST)){
                $msg = "Parsed file ipa! But plist no longer exist";
            }

            if($msg != ""){
                trigger_error($msg, E_USER_WARNING);
                $this->parsed = false;
                //clean extract folder for re-parse
                $rmCommand = sprintf("%s -rf %s", self::RM, $this->extractFolder);
                $this->cmd(self::RM, $rmCommand, Exception::class);
            }


        }

        if(!$this->parsed){
            //unzip ipa, get out Info.plist, *.png
            $imgPattern = "*.png";
            $zipCommand =
                sprintf("%s x %s -aoa -o%s %s %s -r", self::ZIP, $this->ipaFilePath, $this->extractFolder, $imgPattern,
                    self::PLIST);
            $this->cmd(self::ZIP, $zipCommand, ZipException::class);

            //move *.png to $this->extractFolder
            //bcs move can not move to itself, move to tmp folder, then move back
            if(!is_dir(self::OUTPUT_TEMP) && !file_exists(self::OUTPUT_TEMP)){
                mkdir(self::OUTPUT_TEMP, 777, true);
            }
            $dir = scandir($this->extractFolder . I . "Payload");
            $appFolder = $dir[2];
            //make sure that $appFolder NOT contain SPACE
            //believe in preg_match
            if(preg_match('/\s/', $appFolder) === 1){
                $oldFolder = $appFolder;
                $appFolder = $this->removeSpace($oldFolder);
                $renameCommand = sprintf("mv '%s' %s", $this->extractFolder . I . "Payload" . I . $oldFolder,
                    $this->extractFolder . I . "Payload" . I . $appFolder);
                $this->cmd(self::MV, $renameCommand, Exception::class);
            }

            $this->moveFiles($this->extractFolder . I . "Payload" . I . $appFolder, self::PLIST);
            $plistPath = $this->extractFolder . I . self::PLIST;

            $this->moveFiles($this->extractFolder . I . "Payload" . I . $appFolder, $imgPattern);

            $imgFiles = glob($this->extractFolder . I . $imgPattern);

            $failedDecode = true;
            $numOfImgFiles = count($imgFiles);
            $i = 0;
            $pngUncrushed = new PPngUncrush();

            while($failedDecode && $i < $numOfImgFiles){
                $pngUncrushed->setPath($imgFiles[$i]);
                $failedDecode = !$pngUncrushed->decode($this->extractFolder . I . self::APP_ICON);
            }

            if(!$failedDecode){
                trigger_error("decode success", E_USER_NOTICE);
            }

            //set default for $iconPath
            if($failedDecode){
                $copyStatus = copy(__DIR__ . I . "app-icon.png", $this->extractFolder . I . self::APP_ICON);
                if(!$copyStatus){
                    trigger_error("copy default icon failed", E_USER_WARNING);
                }
            }
        }
        //store plist, icon
        $this->plist = new CFPropertyList($plistPath);;
        $this->icon = new Icon($iconPath);
    }

    /**
     * @param string $cmd
     * @param string $command
     * @param Exception $exceptionType
     * @return string
     * @throws Exception
     */
    private function cmd($cmd, $command, $exceptionType){
//        exec($cmd, $out, $resultCode);
//        if($resultCode == 1){
//            //$cmd not installed or not added to environment variable
//            $errMsg = sprintf("%s not installed or not added to environment variable", $cmd);
//            throw new Exception($errMsg);
//        }
//
//        if($resultCode == 0 || $resultCode == 2){
//            exec($command, $out, $resultCode);
//            if($resultCode == 0){
//                return $out;
//            }else{
//                throw new $exceptionType($resultCode);
//            }
//        }
//
//        throw new Exception(self::UN_HANDLE);
        exec($command, $out, $resultCode);
        if($resultCode != 0){
            $msg = sprintf("Error when execute cmd: %s", $command);
            throw new Exception($msg);
        }
    }

    public function getIcon(){
        return $this->icon;

    }

    public function getPlist(){
        return $this->plist;
    }

    public function getBasicInfo(){
        $info = $this->plist->toArray();
        $info[self::ICON_PATH] = $this->icon->getPath();
        return $info;
    }

    /**
     * @param $imgPath
     * @param $imgPattern
     */
    private function moveFiles($imgPath, $imgPattern){
        //MOVE FILE EASILY FAILED when something doesn't match it
        //but it doesn't have HUGE IMPACT on result
        //bypass exception, just exec
        $mvCommand = sprintf("%s %s %s", self::MV, $imgPath . I . $imgPattern, self::OUTPUT_TEMP);
        exec($mvCommand);
//        $this->cmd(self::MV, $mvCommand, Exception::class);
        //now move *.png back to $this->extractFolder
        $mvCommand = sprintf("%s %s %s", self::MV, self::OUTPUT_TEMP . I . $imgPattern, $this->extractFolder);
        exec($mvCommand);
//        $this->cmd("mv", $mvCommand, Exception::class);
    }

    private function removeSpace($name){
        return preg_replace('/\s+/', '-', $name);
    }

    protected function checkParse(){
        if(is_dir($this->extractFolder) && file_exists($this->extractFolder)){
            $this->parsed = true;
        }
    }
}