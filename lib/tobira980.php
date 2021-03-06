<?php declare(strict_types=1);

namespace Tobira980;

class HttpAuthRequiredException extends \Exception {
}

class HttpNoResponseException extends \Exception {
}

class PasswordTimeoutException extends \Exception {
}

class RequestNotOkException extends \Exception {
}

class InvalidResponseException extends \Exception {
}

class InvalidParameterException extends \Exception {
}

final class PreferenceFlags {

	const CARPET_BOOST_OPTIONS = [
		0 => "auto",
		16 => "eco",
		80 => "performance",
	];

	const CLEANING_PASSES_OPTIONS = [
		0 => "auto",
		1024 => "one",
		1025 => "two",
	];
	
	private $rawFlags;
	
	public $carpetBoost;
	public $edgeClean;
	public $cleaningPasses;
	public $alwaysFinish;

	private function __construct(int $rawFlags, string $carpetBoost, bool $edgeClean, string $cleaningPasses, bool $alwaysFinish) {
		$this->rawFlags = $rawFlags;
		$this->carpetBoost = $carpetBoost;
		$this->edgeClean = $edgeClean;
		$this->cleaningPasses = $cleaningPasses;
		$this->alwaysFinish = $alwaysFinish;
	}

	static public function decode(int $rawFlags) : self {
		return new self(
			$rawFlags,
			self::CARPET_BOOST_OPTIONS[$rawFlags & 80],
			!($rawFlags & 2),
			self::CLEANING_PASSES_OPTIONS[$rawFlags & 1025],
			!($rawFlags & 32)
		);
	}

	public function encode() : int {
		return $this->rawFlags;
	}

	public function setCarpetBoost(string $mode) : self {
		if ($mode === "auto") {
			$this->rawFlags &= 65455;
		} else if ($mode === "performance") {
			$this->rawFlags |= 80;
		} else if ($mode === "eco") {
			$this->rawFlags &= 65471;
			$this->rawFlags |= 16;
		} else {
			throw new InvalidParameterException("{$mode} is not a valid carpetBoost mode");
		}
		$this->carpetBoost = $mode;
		return $this;
	}

	public function setEdgeClean(bool $mode = true) : self {
		if ($mode) {
			$this->rawFlags &= 65533;
		} else {
			$this->rawFlags |= 2;
		}
		$this->edgeClean = $mode;
		return $this;
	}

	public function setCleaningPasses(string $mode) : self {
		if ($mode === "auto") {
			$this->rawFlags &= 64510;
		} else if ($mode === "one") {
			$this->rawFlags &= 65534;
			$this->rawFlags |= 1024;
		} else if ($mode === "two") {
			$this->rawFlags |= 1025;
		} else {
			throw new InvalidParameterException("{$mode} is not a valid cleaningPasses mode");
		}
		$this->cleaningPasses = $mode;
		return $this;
	}

	public function setAlwaysFinish(bool $mode = true) : self {
		if ($mode) {
			$this->rawFlags &= 65503;
		} else {
			$this->rawFlags |= 32;
		}
		$this->alwaysFinish = $mode;
		return $this;
	}

}

final class Preferences {

	public $flags;
	public $lang;
	public $timezone;
	public $name;

	private function __construct(PreferenceFlags $flags, int $lang, string $timezone, string $name) {
		$this->flags = $flags;
		$this->lang = $lang;
		$this->timezone = $timezone;
		$this->name = $name;
	}
	
	static public function decode($resp) : self {
		if (!isset($resp->flags)) {
			throw new InvalidResponseException("cannot decode flags in preferences response");
		}
		return new self(
			PreferenceFlags::decode($resp->flags),
			$resp->lang,
			$resp->timezone,
			$resp->name
		);
	}

	public function encode() : array {
		return [
			"flags" => $this->flags->encode(),
			"lang" => $this->lang,
			"timezone" => $this->timezone,
			"name" => $this->name,
		];
	}

}

final class WeekDays {

	public $days = [];
	private $week;

	const NAMES = [
		"sun" => 0,
		"mon" => 1,
		"tue" => 2,
		"wed" => 3,
		"thu" => 4,
		"fri" => 5,
		"sat" => 6,
	];
	
	static private function parseName($day) : int {
		if (is_numeric($day) && ($day >= 0) && ($day <= 6)) {
			return (int)$day;
		} else if (isset(self::NAMES[strtolower($day)])) {
			return self::NAMES[strtolower($day)];
		}
		throw new InvalidParameterException("{$day} is not a valid day of week");
	}
	
	public function __construct(WeekSchedule $week, array $days) {
		$this->week = $week;
		foreach ($days as $name) {
			$this->days[] = self::parseName($name);
		}
	}

	public function setActive(bool $active = true) : self {
		foreach ($this->days as $day) {
			$this->week->sched[$day]["active"] = $active;
		}
		return $this;
	}

	public function setTime(int $hours, int $minutes = 0) : self {
		foreach ($this->days as $day) {
			$this->week->sched[$day]["time"] = $hours . ":" . str_pad((string)$minutes, 2, "0", STR_PAD_LEFT);
		}
		return $this;
	}

}

final class WeekSchedule {

	public $sched;

	private function __construct(array $sched) {
		$this->sched = $sched;
	}

	static public function decode($resp) : self {
		$sched = [];
		for ($d = 0; $d <= 6; $d++) {
			$sched[$d] = [
				"active" => $resp->cycle[$d] === "start",
				"time" => $resp->h[$d] . ":" . str_pad((string)$resp->m[$d], 2, "0", STR_PAD_LEFT),
			];
		}
		return new self($sched);
	}

	public function encode() : array {
		$ret = [
			"cycle" => [],
			"h" => [],
			"m" => [],
		];
		for ($d = 0; $d <= 6; $d++) {
			$ret["cycle"][$d] = $this->sched[$d]["active"] ? "start" : "none";
			$ret["h"][$d] = (int)strtok($this->sched[$d]["time"], ":");
			$ret["m"][$d] = (int)strtok("");
		}
		return $ret;
	}

	public function day($name) : WeekDays {
		return new WeekDays($this, [ $name ]);
	}

	public function days(array $days) : WeekDays {
		return new WeekDays($this, $days);
	}

	public function allDays() : WeekDays {
		return new WeekDays($this, [ 0, 1, 2, 3, 4, 5, 6 ]);
	}

}

final class Robot {

	public $ipAddress;
	public $password;

	private $requestId = 1;

	public function __construct(string $ipAddress, string $password = null) {
		$this->ipAddress = $ipAddress;
		$this->password = $password;
	}

	private function request(string $method, string $command, array $args = null) {
		$reqArgs = [ $command ];
		if (isset($args)) {
			$reqArgs[] = $args;
		}
		$resp = @file_get_contents("https://" . (isset($this->password) ? "user:{$this->password}@" : "") . $this->ipAddress . "/umi", false, stream_context_create([
			"http" => [
				"protocol_version" => "1.1",
				"timeout" => 3,
				"method" => "POST",
				"header" => "Connection: close",
				"content" => json_encode([
					"do" => $method,
					"args" => $reqArgs,
					"id" => $this->requestId++,
				]),
			],
			"ssl" => [
				"verify_peer" => false,
				"verify_peer_name" => false,
			],
		]));
		if ($resp === false) {
			if (!$http_response_header) {
				throw new HttpNoResponseException("no answer from {$this->ipAddress}");
			}
			if (preg_match('/ 401 /', $http_response_header[0])) {
				throw new HttpAuthRequiredException("wrong password");
			}
			throw new InvalidResponseException("cannot send request {$method}/{$command} to robot");
		}
		if (!$ret = @json_decode($resp)) {
			throw new InvalidResponseException("cannot decode response from robot");
		}
		if (!property_exists($ret, "ok")) {
			if (isset($ret->err)) {
				throw new RequestNotOkException("robot responded with error code {$ret->err}", $ret->err);
			}
			throw new RequestNotOkException("robot response is not valid");
		}
		return $ret->ok;
	}
	
	public function getPassword(int $timeout = 60, callable $progress = null) : string {
		$maxRetry = time() + $timeout;
		do {
			try {
				$this->password = $this->request("get", "passwd")->passwd;
				return $this->password;
			} catch (HttpAuthRequiredException $e) {
				if (isset($progress)) {
					$progress();
				}
				sleep(2);
			}
		} while (time() < $maxRetry);
		throw new PasswordTimeoutException("could not get password from robot in {$timeout} second(s)");
	}

	public function getTime() {
		return $this->request("get", "time");
	}

	public function getBbrun() {
		return $this->request("get", "bbrun");
	}

	public function getLangs() {
		return $this->request("get", "langs");
	}

	public function getSys() {
		return $this->request("get", "sys");
	}

	public function getWirelessLastStatus() {
		return $this->request("get", "wllaststat");
	}

	public function getWeek() : WeekSchedule {
		return WeekSchedule::decode($this->request("get", "week"));
	}

	public function setWeek(WeekSchedule $week) {
		return $this->request("set", "week", $week->encode());
	}
	
	public function getPreferences() : Preferences {
		return Preferences::decode($this->request("get", "prefs"));
	}
	
	public function setPreferences(Preferences $prefs) {
		return $this->request("set", "prefs", $prefs->encode());
	}
	
	public function getMission() {
		return $this->request("get", "mssn");
	}

	public function getWirelessConfig() {
		return $this->request("get", "wlcfg");
	}

	public function getWirelessStatus() {
		return $this->request("get", "wlstat");
	}

	public function getCloudConfig() {
		return $this->request("get", "cloudcfg");
	}

	public function getSKU() {
		return $this->request("get", "sku");
	}

	public function start() {
		return $this->request("set", "cmd", [ "op" => "start" ]);
	}

	public function pause() {
		return $this->request("set", "cmd", [ "op" => "pause" ]);
	}

	public function stop() {
		return $this->request("set", "cmd", [ "op" => "stop" ]);
	}
	
	public function resume() {
		return $this->request("set", "cmd", [ "op" => "resume" ]);
	}
	
	public function dock() {
		return $this->request("set", "cmd", [ "op" => "dock" ]);
	}

	public function setTime(array $args) {
		return $this->request("set", "time", $args);
	}

	public function setPtime(array $args) {
		return $this->request("set", "ptime", $args);
	}

}