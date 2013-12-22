<?php

class Sqlite
{
    const DATABASE = 'db/torrents.sqlite';
    protected static $instance;
    protected $db;
    
    public function __construct()
    {
        if (!$this->db = new PDO('sqlite:' . self::DATABASE)) {
            throw new Exception('Unable to open database');
        }
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);        
        
        $q = @$this->db->exec('CREATE TABLE IF NOT EXISTS torrent (hash varchar(50) UNIQUE PRIMARY KEY, title text, date date, seeders int, leechers int)');        
    }
    
    public function instance()
    {
        if (empty(self::$instance)) {
            self::$instance = new Sqlite;
        }        
        return self::$instance;
    }
    
    public static function insert($table, $values = array())
    {
        $columns = implode(', ', array_keys($values));
        $holders = $values;
        array_walk($holders, array('Sqlite', 'bindings'));
        $bindings = implode(', ', $holders);
        $insert = "INSERT OR IGNORE INTO {$table} ({$columns}) 
                    VALUES ({$bindings})";
        //var_dump($insert, $values);
        $stmt = self::instance()->db->prepare($insert);        
     
        foreach ($holders as $k => $v) {
            $stmt->bindValue($v, $values[$k]);
        }
     
        $stmt->execute();
        
        if ($table == 'torrent') {
            $update = "UPDATE torrent SET title = :title, seeders = :seeders, leechers = :leechers WHERE hash = :hash";
            $stmt2 = self::instance()->db->prepare($update);            
            $stmt2->bindValue(':title', $values['title']);
            $stmt2->bindValue(':seeders', $values['seeders']);
            $stmt2->bindValue(':leechers', $values['leechers']);
            $stmt2->bindValue(':hash', $values['hash']);            
            $stmt2->execute();
        }        
    }
    
    protected static function bindings(&$item1, $key)
    {
        $item1 = ":{$key}";
    }
    
    public static function addTorrent($item)
    {
        return self::insert('torrent', $item);
    }
    
    public static function getTorrent($hash = null)
    {
        $sql = 'SELECT * FROM torrent ';
        if ($hash) {
            $sql .= "WHERE hash = '{$hash}' ";            
        }        
        $stmt = self::instance()->db->query($sql);
        $result = $stmt->fetchAll();
 
        return $result;
    }
}