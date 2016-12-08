#!/usr/bin/env php
<?php declare(strict_types=1);

require "../lib/tobira980.php";

$opts = getopt("h:p:c:");
if (!isset($opts["h"]) || !isset($opts["p"]) || !isset($opts["c"])) {
	echo "usage: {$argv[0]} -h <ip_address_of_robot> -p <robot_password> -c <start|pause|stop|resume|dock>\n";
	exit(1);
}

try {
	$r = new Tobira980\Robot($opts["h"], $opts["p"]);
	switch ($opts["c"]) {
		case "start":	$r->start();	break;
		case "pause":	$r->pause();	break;
		case "stop":	$r->stop();		break;
		case "resume":	$r->resume();	break;
		case "dock":	$r->dock();		break;
		default:		throw new Exception("invalid command {$opts['c']}");
	}
} catch (Exception $e) {
	echo "error: {$e->getMessage()}\n";
	exit(1);
}