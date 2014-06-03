<?php

class Transmission
{
    public function __construct($params = array())
    {
        $this->host = '127.0.0.1';
        $this->login = 'transmission';
        $this->password = 'raspberry';
    }
    
    public function getCmd()
    {
        $cmd = 'transmission-remote ' . escapeshellarg($this->host)
               . ' -n ' . escapeshellarg("{$this->login}:{$this->password}")
               . ' ';
        return $cmd;
    }
    
    public function addMagnet($magnet)
    {
        $cmd =  $this->getCmd() . '--add ' . escapeshellarg($magnet);
        var_dump($cmd);
        print `$cmd`;
    }
    
}