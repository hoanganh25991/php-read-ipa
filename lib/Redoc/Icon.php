<?php
namespace Redoc;

class Icon{
    private $path;

    public function __construct($path){
        $this->path = $path;
    }

    public function getPath(){
        return $this->path;
    }
}