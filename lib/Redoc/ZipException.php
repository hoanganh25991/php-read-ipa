<?php
namespace Redoc;

use Exception;

class ZipException extends Exception{

    const NO_ERROR = 0;
    const WARNING = 1;
    const FATAL_ERROR = 2;
    const CLI_ERROR = 7;
    const NOT_ENOUGH_MEMORY = 8;
    const USER_STOP_PROCESS = 255;
    const OTHER_ERROR = 3;

    protected $msgBag = array(
        self::NO_ERROR => "No error",
        self::WARNING => "Warning!  One or more files were locked by some other application, so they were not compressed.",
        self::FATAL_ERROR => "Fatal error",
        self::CLI_ERROR => "Command line error",
        self::NOT_ENOUGH_MEMORY => "Not enough memory for operation",
        self::USER_STOP_PROCESS => "User stopped the process",
        self::OTHER_ERROR => "#apt-get install p7zip-full\n#add 7z to environment variable",
    );

    protected $code;

    public function __construct($code){
        $message = "";
        /** @var Exception $previous */
        $previous = null;
        parent::__construct($message, $code, $previous);
    }

    public function getErrorMsg(){
        $errorCode = self::OTHER_ERROR;
        foreach($this->msgBag as $key => $msg){
            if($this->code == $key){
                $errorCode = $key;
                break;
            }
        }
        return $this->msgBag[$errorCode];
    }
}