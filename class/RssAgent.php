<?php

require_once "XML/RSS.php";
require_once "class/Torrent.php";
require_once "class/Sqlite.php";
require_once "class/Transmission.php";
require_once "class/detectlanguage.php";

use \DetectLanguage\DetectLanguage;

class RssAgent
{
    const CONFIG_FILE = 'config.php';    
    const WATCH_DIR = 'tmp';
    
    protected $config = array();

    public function __construct()
    {
        $this->loadConfig();
        DetectLanguage::setApiKey($this->config->language_api_key);
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
        $transmission = new Transmission;
        $magnets = $this->getMagnetLinks();
        foreach ($magnets as $magnet) {
            $hash = Torrent::magnet2hash($magnet);
            $title = Torrent::magnet2torrent($magnet /*,self::WATCH_DIR*/);
            
            if ($this->preTitleCensor($title)) {
                continue;
            }
            
            $exists = Sqlite::getTorrent($hash);
            $callback = array($this, 'isItTheSame');
            $exists2 = false;
            if (empty($exists)) {
                $exists2 = Sqlite::doesTitleExist($title, $callback);
            }
            
            //var_dump($exists);
            if (empty($exists) && empty($exists2)) {            
                if ($this->postTitleCensor($title)) {
                    echo "Censoring "  . substr($title, 0, 50) . " \n";
                    //continue;
                } else {
                    echo "Adding "  . substr($title, 0, 50) . " ";
                    $transmission->addMagnet($magnet);
                }
            } else {
                $e2 = $exists2 ? 't' : 'f';
                echo "Exists 2:{$e2} "  . substr($title, 0, 40) . "..\n";
                continue;                
            }            
            
            try {
                $data = Torrent::scrape($hash);            
                $data = $data[$hash];
            } catch (Exception $e) {
                echo "Scrape failed\n";
                $data['seeders'] = 0;
                $data['leechers'] = 0;
            }
            
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
    
    public function isItTheSame($title1, $title2)
    {
        $t1 = $this->parseTitle($title1);
        $t2 = $this->parseTitle($title2);
        
        if (strtolower($t1->title) == strtolower($t2->title)) {
            return true;
        }
    }
    
    /**
     * returns object [title, episode, year, source, codec, audio, pirate]
     */
    public static function parseTitle($title)
    {
        $result = (object) [];
        $parts = preg_split("/[^a-z0-9]+/i", $title);
        $title = array();
        $titleDone = 0;
        foreach ($parts as $i => $part) {
            // Check if $part is a year
            if ($i > 0 && ctype_digit($part) && $part > 1950 && $part <= date('Y')) {
                $titleDone = 1;
                $result->year = $part;
            }
            
            if ($i > 0 && in_array(strtolower($part), ['extended', '720p', '1080p', 'HD'])) {
                $titleDone = 1;
            }
            
            if (preg_match("/s\d{2}e\d{2}/i", $part)) {
                $title[] = $part;
                $titleDone = 1;                
                $result->episode = strtolower($part);
            }
            
            if (!$titleDone) {
                $title[] = $part;
            }
        }
        $result->title = implode(' ', $title);
        return $result;
    }
    
    public function testSanitize()
    {
        $db = Sqlite::instance();
        $torrents = $db->getTorrent();
        foreach ($torrents as $t) {
            echo "{$t->title}\n";
            $r = $this->parseTitle($t->title);
            echo "{$r->title}\n";
            echo "\n\n";
        }
    }
    
    public function preTitleCensor($title)
    {
        $titleCase = $title;
        $title = strtolower($title);
    
        if ($this->checkForWords($title, ["hindi", "spanish", "german", "xxx", "porn", "french"])) {
            return true;
        }
        
        return false;
    }
    
    public function checkForWords($haystack, $needles = array())
    {
        $haystack = strtolower($haystack);
        foreach ($needles as $needle) {
            if (strpos($haystack, strtolower($needle)) !== false) {
                return true;
            }
        }
        return false;
    }
    
    public function postTitleCensor($title)
    {
        $titleCase = $title;
        $title = strtolower($title);
    
        $r = $this->parseTitle($title);
        $languageCode = DetectLanguage::simpleDetect($r->title);
        echo "[Lang:{$languageCode} of {$r->title}]\n";
        
        if ($languageCode != 'en') {            
            return true;
        }
    
        return false;
    }
}
