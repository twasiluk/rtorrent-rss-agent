<?php

require_once "XML/RSS.php";

class RssAgent
{
    const CONFIG_FILE = 'config.php';    
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
        foreach ($this->config['feeds'] as $cfg) {
            $magnets = array_merge($magnets, $this->parseFeed($cfg->url));
        }
        return $magnets;
    }
    
    public function parseFeed($url)
    {
        $items = array();
        $rss = new XML_RSS($url);
        $rss->parse();
        echo "Feed: $url\n";
        foreach ($rss->getItems() as $item) {
            echo "Link: {$item['link']}\n";
        }
        echo "\n";
        
        return $items;
    }
}
