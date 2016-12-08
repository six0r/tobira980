#!/usr/bin/env php
<?php declare(strict_types=1);

require __DIR__ . "/../lib/tobira980.php";

$opts = getopt("h:");
if (!isset($opts["h"])) {
	echo "usage: {$argv[0]} -h <ip_address_of_robot>\n";
	exit(1);
}

try {
	echo "Trying to get password, please long-press the home button on the robot until you hear a signal .";
	$pass = (new Tobira980\Robot($opts["h"]))->getPassword(120, function() { echo " ."; });
	echo "\n";
	echo "Got password : {$pass}\n";
} catch (Exception $e) {
	echo "\n";
	echo "error: {$e->getMessage()}\n";
	exit(1);
}