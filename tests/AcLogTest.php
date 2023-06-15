<?php

declare(strict_types=1);

require __DIR__ . '/../src/AcLog.php';

use xy2z\AcLog\AcLog;
use PHPUnit\Framework\TestCase;

class TestSimpleClass {
	public $foo = 'bar';
}

class AcLogTest extends TestCase {
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
		$filename = date('Y-m-d-') . uniqid() . '.txt';

		$aclog = new AcLog(
			log_dir: $this->logdir,
			output_method: AcLog::VAR_DUMP,
			filename: $filename,
			header_date_format: 'Ymd',
			line_breaks_between_header: 6,
			log_date_format: 'YmHi'
		);

		$log_path = $this->logdir . $filename;

		// Log dir exists
		$this->assertDirectoryExists($this->logdir);

		// Log file exists (assert filename option works)
		$this->assertFileExists($log_path);

		// Assert output method is "VAR_DUMP"
		$aclog->log("vardump");
		$this->assertTrue(static::file_contains($log_path, 'string(7) "vardump"'));

		// Test option: header is logged and correct $header_date_format
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

	public function testDisableHeader(): void {
		$aclog = new AcLog(
			log_dir: $this->logdir,
			log_header: false,
		);

		// Validate logging works.
		$aclog->log('hello.');
		$this->assertTrue(static::file_contains($aclog->get_log_file(), " | 'hello.'"));

		// Validate there's no header.
		$this->assertFalse(static::file_contains($aclog->get_log_file(), '====='));
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
		$aclog = new AcLog(
			log_dir: $this->logdir,
			output_method: AcLog::PRINT_R,
		);

		$aclog->add_log_append_callback(function () {
			return 'callback-1.';
		});
		$aclog->add_log_append_callback(function () {
			return 'callback-2.';
		});

		$aclog->add_log_append_callback(function () {
			// test array
			return ['callback-array-3.0', 'callback-array-3.1'];
		});

		$aclog->add_log_append_callback(function () {
			// test stdClass/array object
			return (object) ['testing' => 'callback-object-4.0'];
		});

		$aclog->add_log_append_callback(function () {
			// test class object
			return new TestSimpleClass();
		});

		$aclog->log('hello.', 'andgoodbye.', ['array.']);

		// echo file_get_contents($aclog->get_log_file());
		$this->assertTrue(static::file_contains($aclog->get_log_file(), 'callback-1.'));
		$this->assertTrue(static::file_contains($aclog->get_log_file(), 'callback-2.'));
		$this->assertTrue(static::file_contains($aclog->get_log_file(), "[0] => callback-array-3.0"));
		$this->assertTrue(static::file_contains($aclog->get_log_file(), "[1] => callback-array-3.1"));
		$this->assertTrue(static::file_contains($aclog->get_log_file(), "[testing] => callback-object-4.0"));
		$this->assertTrue(static::file_contains($aclog->get_log_file(), "TestSimpleClass Object"));
		$this->assertTrue(static::file_contains($aclog->get_log_file(), "[foo] => bar"));
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
