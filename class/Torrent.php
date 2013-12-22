<?php

class Torrent
{
    public static function magnet2torrent($magnet, $directory)
    {
        $torrent = "d10:magnet-uri" . strlen($magnet) . ":{$magnet}e";
        parse_str($magnet, $vars);
        $name = trim($vars['dn']);
        file_put_contents("{$directory}/{$name}.torrent", $torrent);
    }
}