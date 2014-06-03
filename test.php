<?php

require_once 'class/RssAgent.php';

$r = new RssAgent;
$l = $r->importTorrents();
//$r->testSanitize();
