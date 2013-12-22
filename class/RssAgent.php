<?php

require_once "XML/RSS.php";
require_once "class/Torrent.php";
require_once "class/Sqlite.php";

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
        $db = Sqlite::instance();
        $magnets = $this->getMagnetLinks();
        foreach ($magnets as $magnet) {
            $hash = Torrent::magnet2hash($magnet);
            $title = Torrent::magnet2torrent($magnet, self::WATCH_DIR);            
            $exists = Sqlite::getTorrent($hash);
            //var_dump($exists);
            if (empty($exists)) {
                echo "Adding "  . substr($title, 0, 50) . " ";
            } else {
                continue;
                //echo "Exists "  . substr($title, 0, 12) . "..";
            }            
            
            $data = Torrent::scrape($hash);            
            $data = $data[$hash];
            echo "S:{$data['seeders']} L:{$data['leechers']}\n";
            //var_dump($data);
            Sqlite::addTorrent(array(
                'hash' => $hash,
                'title' => $title,
                'seeders' => $data['seeders'],
                'leechers' => $data['leechers'],
                'date' => date('Y-m-d'),
            ));
        }
    }
}
