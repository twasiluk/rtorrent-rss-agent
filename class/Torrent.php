<?php

require_once 'class/PHP-Torrent-Scraper/udptscraper.php';

class Torrent
{
    const TRACKER = 'udp://tracker.openbittorrent.com:80';

    public static function magnet2torrent($magnet, $directory = null)
    {
        $torrent = "d10:magnet-uri" . strlen($magnet) . ":{$magnet}e";
        parse_str($magnet, $vars);
        $name = trim($vars['dn']);
        if ($directory) {
            file_put_contents("{$directory}/{$name}.torrent", $torrent);
        }
        return $name;
    }
    
    public static function scrape($hash)
    {
        $timeout = 2;
        $scraper = new udptscraper($timeout);
        $ret = $scraper->scrape(self::TRACKER, array($hash));
        //print_r($ret);
        return $ret;        
    }
    
    public static function magnet2hash($magnet)
    {
        preg_match("/btih\:(.{40})/", $magnet, $parts);
        //var_dump($parts, $magnet);
        return $parts[1];
    }
}