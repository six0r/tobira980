# tobira980

Tobira980 is an unofficial PHP 7+ library for the Roomba 980 cleaning robot.

It is heavily based on the reverse engineering work of [koalazak](https://github.com/koalazak) and his NodeJS library [dorita980](https://github.com/koalazak/dorita980).

# Usage

In order to take control of your robot you will have to request its password first :

```php
require "tobira980.php";

echo "Trying to get password, please long-press the home button on the robot until you hear a signal ...\n";
$pass = (new Tobira980\Robot("192.168.0.12"))->getPassword();
echo "Got password : {$pass}\n";
```

Then you may use the password to send commands and request information from the robot :

```php
require "tobira980.php";

// Connect to the robot using its IP address and password
$r = new Tobira980\Robot("192.168.0.12", "_my_password_");

// Dump status
var_dump($r->getMission());

// Fetch configuration
$prefs = $r->getPreferences();
// Change some values
$prefs->flags->setCarpetBoost("auto")->setEdgeClean(true)->setCleaningPasses("auto")->setAlwaysFinish(true);
// Send new configuration to the robot
$r->setPreferences($prefs);

// Start cleaning cycle
$r->start();
sleep(10);

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

## Error handling

If an error occurs, the following exceptions are thrown by the methods above :

- `HttpNoResponseException` : the library cannot make a HTTPS request to the robot
- `HttpAuthRequiredException` : the provided password was not accepted by the robot
- `PasswordTimeoutException` : only thrown by Robot::getPassword()
- `RequestNotOkException` : the robot did not respond successfuly to the request
- `InvalidResponseException` : the robot response was not understood by the library
- `InvalidParameterException` : one or more parameters are not valid
