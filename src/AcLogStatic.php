<?php

namespace xy2z\AcLog;

// require_once __DIR__ . '/AcLog.php';

use xy2z\AcLog\AcLog;
use TypeError;

abstract class AcLogStatic {
	protected static AcLog $aclog;

	// public static function setup(string $log_dir) {
	public static function setup(mixed $args): void {
		if (is_string($args)) {
			// Only contains log_dir.
			static::$aclog = new AcLog($args);
		} else {
			static::$aclog = new AcLog(...$args);
		}
	}

	/**
	 * Magic method for passing all method calls to the AcLog class
	 * @param string $name
	 * @param array<mixed> $arguments
	 */
	public static function __callStatic(string $name, array $arguments): mixed {
		// Pass all method calls to the AcLog object.
		$var = null;
		try {
			$var = call_user_func_array([static::$aclog, $name], $arguments);
		} catch (TypeError $e) {
			// If they try to access protected/private methods.
			error_log($e->getMessage());
		}

		return $var;
	}
}
