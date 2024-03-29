<?php

namespace xy2z\AcLog;

use DateTime;
use Exception;

class AcLog {
	protected string $log_file;
	protected mixed /* resource */  $handle;
	protected bool $is_header_logged = false;
	protected int $count_logged = 0;
	protected bool $is_static = false; // via AcLogStatic.

	public const VAR_EXPORT = 0;
	public const PRINT_R = 1;
	public const VAR_DUMP = 2;

	/** @var array<callable> */
	protected array $log_append_callbacks = [];


	public function __construct(
		protected string $log_dir,

		// Options:
		protected int $output_method = self::VAR_EXPORT,
		protected string|null $filename = null,
		protected string $header_date_format = 'r',
		protected int|null $file_chmod = null, // int (octal)
		protected int $line_breaks_between_header = 2,
		protected bool $include_trace = true,
		protected string $log_date_format = 'H:i:s.v P',
		protected bool $log_header = true
	) {
		if (!is_dir($this->log_dir)) {
			mkdir($this->log_dir);
		}

		if (empty($this->filename)) {
			// Set default filename.
			$this->filename = date('Y-m-d') . '.log';
		}

		$this->log_file = $log_dir . '/' . $this->filename;

		$this->handle = fopen($this->log_file, 'a');

		if (!is_null($this->file_chmod)) {
			chmod($this->log_file, $this->file_chmod);
		}
	}

	public function destroy(): void {
		// Add line breaks, only if anything was logged.
		if (!is_resource($this->handle)) {
			return;
		}

		if ($this->count_logged) {
			fwrite($this->handle, str_repeat(PHP_EOL, $this->line_breaks_between_header));
		}

		fclose($this->handle);
	}

	public function __destruct() {
		$this->destroy();
	}

	protected function print_header(): void {
		if ($this->log_header && !$this->is_header_logged) {
			fwrite($this->handle, str_repeat('=', 10) . "[ '" . date($this->header_date_format) . "' ]" . str_repeat('=', 10)  . PHP_EOL);
			$this->is_header_logged = true;
		}
	}

	public function log(): void {
		$this->count_logged++;
		$this->print_header();

		foreach (func_get_args() as $arg) {
			$trace = '';
			$datetime = '';

			if ($this->include_trace) {
				$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
				$caller = ($this->is_static) ? $backtrace[2] : $backtrace[0];

				if (isset($caller['file']) && isset($caller['line'])) {
					// When using AcLogStatic, 'file' and 'line' might not be set.
					$trace = '[' . $caller['file'] . ':' . $caller['line'] . '] ';
				}
			}

			if ($this->log_date_format) {
				$datetime_object = new DateTime('now');
				$datetime = $datetime_object->format($this->log_date_format) . ' | ';
			}

			fwrite($this->handle, $trace . $datetime . $this->get_var_output($arg));
		}

		// Append callback to each log() call.
		foreach ($this->log_append_callbacks as $callback) {
			$value = call_user_func($callback);
			fwrite($this->handle, $this->get_var_output($value));
		}
	}

	protected function get_var_output(mixed $var): string|bool {
		if ($this->output_method === self::VAR_EXPORT) {
			return (var_export($var, true) . PHP_EOL);
		}
		if ($this->output_method === self::PRINT_R) {
			return (print_r($var, true) . PHP_EOL);
		}
		if ($this->output_method === self::VAR_DUMP) {
			// var_dump() function adds a PHP_EOL itself.
			ob_start();
			var_dump($var);
			return ob_get_clean();
		}

		throw new Exception('Unknown output_method: ' . $this->output_method);
	}

	public function clear_file(): void {
		// Clear all content in the current log file.
		file_put_contents($this->log_file, '');
	}

	public function add_log_append_callback(callable $callback): void {
		$this->log_append_callbacks[] = $callback;
	}

	public function get_log_dir(): string {
		return $this->log_dir;
	}

	public function get_log_file(): string {
		return $this->log_file;
	}

	public function set_static(bool $val): void {
		// This is automatically set on new AcLog instances via AcLogStatic::setup().
		$this->is_static = $val;
	}
}
