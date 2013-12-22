<?php

require_once 'class/RssAgent.php';

$r = new RssAgent;
$l = $r->getMagnetLinks();
var_dump($l);