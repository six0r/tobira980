# tobira980

Tobira980 is an unofficial PHP 7+ library for the Roomba 980 cleaning robot.

It is heavily based on the reverse engineering work of [koalazak](https://github.com/koalazak) and his NodeJS library [dorita980](https://github.com/koalazak/dorita980).

# Usage

In order to take control of your robot you will have to request its password first :

```php
require "tobira980.php";

// Connect to the robot using its IP address
$r = new Tobira980\Robot("192.168.0.12");

// Fetch password from the robot
echo "Trying to get password, please long-press the home button on the robot until you hear a signal ...\n";
echo "Got password : " . $r->getPassword();
```

Then you may use the password to send commands and request information from the robot :

```php
require "tobira980.php";

// Connect to the robot using its IP address and password
$r = new Tobira980\Robot("192.168.0.12", "_my_password_");

// Fetch configuration
$prefs = $r->getPreferences();
// Change some values
$prefs->flags->setCarpetBoost("auto")->setEdgeClean(true)->setCleaningPasses("auto")->setAlwaysFinish(true);
// Send configuration to the robot
$r->setPreferences($prefs);

// Fetch week schedule
$week = $r->getWeek();
// Schedule cleaning cycle every work day at 8:30 AM and none on weekends
$week->days([ "mon", "tue", "wed", "thu", "fri" ])->setTime(8, 30)->setActive();
$week->days([ "sat", "sun" ])->setActive(false);
// Send schedule to robot
$r->setWeek($week);

// Start cleaning cycle
$r->start();
sleep(10);

// Dump status
var_dump($r->getMission());

// Stop cycle
$r->stop();
sleep(10);

// Back to the irons
$r->dock();
```

# Methods

All the methods below - unless specified otherwise - return the decoded response from the robot without any processing.

## Robot object

- `Robot::getPassword(int $timeout = 60, callable $progress = null) : string`
- `Robot::getTime()`
- `Robot::getBbrun()`
- `Robot::getLangs()`
- `Robot::getSys()`
- `Robot::getWirelessLastStatus()`
- `Robot::getWeek() : WeekSchedule`
- `Robot::setWeek(WeekSchedule $week)`
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
- `Robot::setTime(array $args)`
- `Robot::setPtime(array $args)`

## PreferenceFlags object

- `PreferenceFlags::setCarpetBoost(string $mode) : self`
- `PreferenceFlags::setEdgeClean(bool $mode = true) : self`
- `PreferenceFlags::setCleaningPasses(string $mode) : self`
- `PreferenceFlags::setAlwaysFinish(bool $mode = true) : self`

## WeekSchedule object

- `WeekSchedule::day($name) : WeekDays`
- `WeekSchedule::days(array $days) : WeekDays`
- `WeekSchedule::allDays() : WeekDays`

## WeekDays object

- `WeekDays::setActive(bool $active = true) : self`
- `WeekDays::setTime(int $hours, int $minutes = 0) : self`

## Error handling

If an error occurs, the following exceptions are thrown by the methods above :

- `HttpNoResponseException` : the library cannot make a HTTPS request to the robot
- `HttpAuthRequiredException` : the provided password was not accepted by the robot
- `PasswordTimeoutException` : only thrown by Robot::getPassword()
- `RequestNotOkException` : the robot did not respond successfuly to the request
- `InvalidResponseException` : the robot response was not understood by the library
- `InvalidParameterException` : one or more parameters are not valid