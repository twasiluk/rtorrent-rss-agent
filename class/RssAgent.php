<?php

require_once "XML/RSS.php";
require_once "class/Torrent.php";

class RssAgent
{
    const CONFIG_FILE = 'config.php';    
    const WATCH_DIR = 'tmp';
    
    protected $config = array();

    public function __construct()
    {
        $this->loadConfig();
    }
    
    protected function loadConfig($file = self::CONFIG_FILE)
    {
        $this->config = include $file;
    }
    
    public function getMagnetLinks()
    {
        $magnets = array();
        foreach ($this->config->feeds as $cfg) {
            $magnets = array_merge($magnets, $this->parseFeed($cfg->url));
        }
        return $magnets;
    }
    
    public function parseFeed($url)
    {
        $items = array();
        $rss = new XML_RSS($url);
        $rss->parse();
        echo "Parsing {$url}\n";
        foreach ($rss->getItems() as $item) {
            $items[] = $item['link'];
        }        
        return $items;
    }
    
    public function importTorrents()
    {
        $magnets = $this->getMagnetLinks();
        foreach ($magnets as $magnet) {
            Torrent::magnet2torrent($magnet, self::WATCH_DIR);
        }
    }
}
