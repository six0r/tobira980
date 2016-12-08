# tobira980

Tobira980 is an unofficial PHP 7+ library for the Roomba 980 cleaning robot.

It is heavily based on the reverse engineering work of [koalazak](https://github.com/koalazak) and his NodeJS library [dorita980](https://github.com/koalazak/dorita980).

# Usage

In order to take control of your robot you will have to request its password first :

```php
<?php 

require "tobira980.php";

try {
	echo "Trying to get password, please long-press the home button on the robot until you hear a signal ...\n";
	$pass = (new Tobira980\Robot("192.168.0.12"))->getPassword();
	echo "Got password : {$pass}\n";
} catch (Exception $e) {
	echo "error: {$e->getMessage()}\n";
	exit(1);
}
```

Then you may use the password to send commands and request information from the robot :

```php
<?php

require "tobira980.php";

try {
	$r = new Tobira980\Robot("192.168.0.12", "_my_password_");
	echo "Robot status :\n";
	var_dump($r->getMission());
	echo "Start ...\n";
	$r->start();
	sleep(10);
	echo "Stop ...\n";
	$r->stop();
	sleep(10);
	echo "Dock ...\n";
	$r->dock();
} catch (Exception $e) {
	echo "error: {$e->getMessage()}\n";
	exit(1);
}
```

# Methods

## Robot object

- `Robot::getPassword(int $timeout = 60, callable $progress = null) : string`
- `Robot::getTime()`
- `Robot::getBbrun()`
- `Robot::getLangs()`
- `Robot::getSys()`
- `Robot::getWirelessLastStatus()`
- `Robot::getWeek()`
- `Robot::getPreferences() : Preferences`
- `Robot::setPreferences(Preferences $prefs)`
- `Robot::getMission()`
- `Robot::getWirelessConfig()`
- `Robot::getWirelessStatus()`
- `Robot::getCloudConfig()`
- `Robot::getSKU()`
- `Robot::start()`
- `Robot::pause()`
- `Robot::stop()`
- `Robot::resume()`
- `Robot::dock()`
- `Robot::setWeek(array $args)`
- `Robot::setTime(array $args)`
- `Robot::setPtime(array $args)`

## PreferenceFlags object

- `PreferenceFlags::setCarpetBoost(string $mode) : self`
- `PreferenceFlags::setEdgeClean(bool $mode) : self`
- `PreferenceFlags::setCleaningPasses(string $mode) : self`
- `PreferenceFlags::setAlwaysFinish(bool $mode) : self`