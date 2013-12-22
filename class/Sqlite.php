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
        
        $q = @$this->db->exec('CREATE TABLE IF NOT EXISTS torrent (hash varchar(50), title text, date date, seeders int, leechers int, PRIMARY KEY (hash))');        
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
        $stmt = self::instance()->db->prepare($insert);        
     
        foreach ($holders as $k => $v) {
            $stmt->bindParam($v, $values[$k]);
        }
     
        $stmt->execute();
        
        if ($table == 'torrent') {
            $update = "UPDATE torrent SET title = :title WHERE hash = :hash";
            $stmt2 = self::instance()->db->prepare($update);        
            $stmt2->bindParam(':title', $values['title']);
            $stmt2->bindParam(':hash', $values['hash']);           
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
    
    public static function listTorrent()
    {
        // Select all data from file db messages table 
        $result = self::instance()->db->query('SELECT * FROM torrent');
 
        // Loop thru all data from messages table 
        // and insert it to file db
        foreach ($result as $m) {
          // Bind values directly to statement variables
          $stmt->bindValue(':hash', $m['hash'], SQLITE3_TEXT);
          $stmt->bindValue(':title', $m['title'], SQLITE3_TEXT);
          $stmt->bindValue(':date', $m['message'], SQLITE3_TEXT);
     
          // Format unix time to timestamp
          $formatted_time = date('Y-m-d H:i:s', $m['time']);
          $stmt->bindValue(':time', $formatted_time, SQLITE3_TEXT);
     
          // Execute statement
          $stmt->execute();
        }
    }
}