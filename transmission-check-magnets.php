<?php

require 'class/Torrent.php';
require 'class/Transmission.php';

$magnet = 'magnet:?xt=urn:btih:8412aae1dd0d81ffcc46fa537b2d583425fb346a&dn=Clubbed+to+Death+-+Rob+Dougan+%5BRH%5D&tr=udp%3A%2F%2Ftracker.openbittorrent.com%3A80&tr=udp%3A%2F%2Ftracker.publicbt.com%3A80&tr=udp%3A%2F%2Ftracker.istole.it%3A6969&tr=udp%3A%2F%2Fopen.demonii.com%3A1337';

$tr = new Transmission;
$tr->addMagnet($magnet);

$t = new Torrent;
$t->magnet2torrent($magnet, '/home/pi');