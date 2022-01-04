<?php

declare(strict_types=1);

require __DIR__ . '/../src/AcLog.php';

use xy2z\AcLog\AcLog;
use PHPUnit\Framework\TestCase;

class AcLogTest extends TestCase {
	private $logdir;

	private static function file_contains(string $path, string $needle) {
		$log_content = file_get_contents($path);
		// var_dump($log_content);
		// var_dump('find: ' . $needle);
		return strpos($log_content, $needle) !== false;
		// return (strpos($log_content, $needle) !== false));
	}

	private function cleanup() {
		if (!is_dir($this->logdir)) {
			return;
		}

		// Remove log dir.
		foreach (scandir($this->logdir) as $item) {
			if (($item === '.') || ($item === '..')) {
				continue;
			}

			// Remove the file.
			unlink($this->logdir . $item);
		}

		// Remove the dir, now that it should be empty.
		rmdir($this->logdir);
	}

	public function setUp(): void {
		$this->logdir = __DIR__ . '/logs/';
		// $this->cleanup();
	}

	public function tearDown(): void {
		$this->cleanup();
	}

	public function testSimple(): void {
		$aclog = new AcLog($this->logdir);
		$this->assertDirectoryExists($this->logdir);
		$this->assertFileExists($this->logdir . date('Y-m-d') . '.log');
	}

	public function testOptions(): void {
		// Test as many options as possible.
		$aclog = new AcLog(
			log_dir: $this->logdir,
			output_method: AcLog::VAR_DUMP,
			filename_date_format: 'Ymd',
			header_date_format: 'Ymd',
			line_breaks_between_header: 6,
			log_date_format: 'YmHi'
		);

		$log_path = $this->logdir . date('Ymd') . '.log';

		// Log dir exists
		$this->assertDirectoryExists($this->logdir);

		// Log file exists (assert filename option works)
		$this->assertFileExists($log_path);

		// Assert output method is "VAR_DUMP"
		$aclog->log("vardump");
		$this->assertTrue(static::file_contains($log_path, 'string(7) "vardump"'));

		// Test option: header_date_format
		$this->assertTrue(static::file_contains($log_path, "==========[ '" . date('Ymd') . "' ]=========="));

		// Test trace is there.
		$this->assertTrue(static::file_contains($log_path, '[' . __FILE__));

		// Test option: log_date_format
		$this->assertTrue(static::file_contains($log_path, '] ' . date('YmHi') . ' | '));

		// Test method: get_log_dir()
		$this->assertSame($aclog->get_log_dir(), $this->logdir);

		// Test method: get_log_file()
		$this->assertSame(realpath($aclog->get_log_file()), realpath($log_path));

		// ------------
		// Lastly, test option: line_breaks_between_header
		unset($aclog); // calls the destruct().
		$this->assertTrue(static::file_contains($log_path, str_repeat(PHP_EOL, 6)));
	}

	public function testLogFound(): void {
		$aclog = new AcLog($this->logdir);
		$this->assertFileExists($this->logdir . date('Y-m-d') . '.log');

		// Log the string.
		$string = 'find-this-string.';
		$aclog->log($string);

		// Make sure the string exists in the log file.
		$this->assertTrue(static::file_contains($this->logdir . date('Y-m-d') . '.log', "'" . $string . "'"));
	}

	public function testCallbacks(): void {
		$aclog = new AcLog($this->logdir);

		$aclog->add_log_append_callback(function () {
			return 'callback-1.';
		});
		$aclog->add_log_append_callback(function () {
			return 'callback-2.';
		});

		$aclog->log('hello.', 'andgoodbye.', ['array.']);
		$this->assertTrue(static::file_contains($aclog->get_log_file(), 'callback-1.'));
		$this->assertTrue(static::file_contains($aclog->get_log_file(), 'callback-2.'));
		$this->assertTrue(static::file_contains($aclog->get_log_file(), 'hello.'));
		$this->assertTrue(static::file_contains($aclog->get_log_file(), 'andgoodbye.'));
		$this->assertTrue(static::file_contains($aclog->get_log_file(), 'array.'));
	}

	public function testClearFile(): void {
		$aclog = new AcLog($this->logdir);
		$aclog->log('hello.');
		$this->assertTrue(static::file_contains($aclog->get_log_file(), 'hello.'));
		$this->assertNotEmpty(file_get_contents($aclog->get_log_file()));

		$aclog->clear_file();
		$this->assertEmpty(file_get_contents($aclog->get_log_file()));
	}
}
