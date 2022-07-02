<?php

declare(strict_types=1);

// require_once __DIR__ . '/../src/AcLog.php';
require_once __DIR__ . '/../src/AcLogStatic.php';

use xy2z\AcLog\AcLog;
use xy2z\AcLog\AcLogStatic;
use PHPUnit\Framework\TestCase;

class AcLogStaticTest extends TestCase {
	private string $logdir;

	private static function file_contains(string $path, string $needle): bool {
		$log_content = file_get_contents($path);
		return strpos($log_content, $needle) !== false;
	}

	private function cleanup(): void {
		if (!is_dir($this->logdir)) {
			return;
		}

		// Remove log dir.
		foreach (scandir($this->logdir) as $item) {
			if (($item === '.') || ($item === '..')) {
				continue;
			}

			// Remove the file.
			$unlink = unlink($this->logdir . $item);
		}

		// Remove the dir, now that it should be empty.
		rmdir($this->logdir);
	}

	public function setUp(): void {
		$this->logdir = __DIR__ . '/logs-static/';
	}

	public function tearDown(): void {
		// We must manually destruct() the object, so the file is closed (fclose())
		// or else we cannot delete the test files and directory.
		AcLogStatic::destroy();
		$this->cleanup();
	}

	public function testSimple(): void {
		AcLogStatic::setup($this->logdir);
		$this->assertDirectoryExists($this->logdir);
		$this->assertFileExists($this->logdir . date('Y-m-d') . '.log');
	}

	public function testOptions(): void {
		// Test as many options as possible.
		$filename = date('Y-m-d-') . uniqid() . '.txt';

		AcLogStatic::setup([
			'log_dir' => $this->logdir,
			'output_method' => AcLog::VAR_DUMP,
			'filename' => $filename,
			'header_date_format' => 'Ymd',
			'line_breaks_between_header' => 6,
			'log_date_format' => 'YmHi'
		]);
		AcLogStatic::set_testing(true);

		$log_path = $this->logdir . $filename;

		// Log dir exists
		$this->assertDirectoryExists($this->logdir);

		// Log file exists (assert filename option works)
		$this->assertFileExists($log_path);

		// Assert output method is "VAR_DUMP"
		AcLogStatic::log("vardump");
		$this->assertTrue(static::file_contains($log_path, 'string(7) "vardump"'));

		// Test option: header_date_format
		$this->assertTrue(static::file_contains($log_path, "==========[ '" . date('Ymd') . "' ]=========="));

		// Test trace is there.
		$this->assertTrue(static::file_contains($log_path, '[' . __FILE__));

		// Test option: log_date_format
		$this->assertTrue(static::file_contains($log_path, '] ' . date('YmHi') . ' | '));

		// Test method: get_log_dir()
		$this->assertSame(AcLogStatic::get_log_dir(), $this->logdir);

		// Test method: get_log_file()
		$this->assertSame(realpath(AcLogStatic::get_log_file()), realpath($log_path));
	}

	public function testLogFound(): void {
		AcLogStatic::setup($this->logdir);
		AcLogStatic::set_testing(true);
		$this->assertFileExists($this->logdir . date('Y-m-d') . '.log');

		// Log the string.
		$string = 'find-this-string.';
		AcLogStatic::log($string);

		// Make sure the string exists in the log file.
		$this->assertTrue(static::file_contains($this->logdir . date('Y-m-d') . '.log', "'" . $string . "'"));
	}

	public function testCallbacks(): void {
		AcLogStatic::setup($this->logdir);
		AcLogStatic::set_testing(true);

		AcLogStatic::add_log_append_callback(function () {
			return 'callback-1.';
		});
		AcLogStatic::add_log_append_callback(function () {
			return 'callback-2.';
		});

		AcLogStatic::log('hello.', 'andgoodbye.', ['array.']);
		$this->assertTrue(static::file_contains(AcLogStatic::get_log_file(), 'callback-1.'));
		$this->assertTrue(static::file_contains(AcLogStatic::get_log_file(), 'callback-2.'));
		$this->assertTrue(static::file_contains(AcLogStatic::get_log_file(), 'hello.'));
		$this->assertTrue(static::file_contains(AcLogStatic::get_log_file(), 'andgoodbye.'));
		$this->assertTrue(static::file_contains(AcLogStatic::get_log_file(), 'array.'));
	}


	public function testClearFile(): void {
		AcLogStatic::setup($this->logdir);
		AcLogStatic::set_testing(true);

		AcLogStatic::log('hello.');
		$this->assertTrue(static::file_contains(AcLogStatic::get_log_file(), 'hello.'));
		$this->assertNotEmpty(file_get_contents(AcLogStatic::get_log_file()));

		AcLogStatic::clear_file();
		$this->assertEmpty(file_get_contents(AcLogStatic::get_log_file()));
	}
}
