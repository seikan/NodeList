<?php

namespace Error;

class Handler
{
	protected $callback;

	public function __construct($callback = null)
	{
		if (!\is_callable($callback)) {
			throw new \Exception('ErrorHandler: Callback function is not defined.');
		}

		$this->callback = $callback;

		// Register error handlers
		set_error_handler([$this, 'errorHandler']);
		set_exception_handler([$this, 'exceptionHandler']);
		register_shutdown_function([$this, 'shutdownHandler']);
	}

	public function errorHandler($code, $message, $file, $line)
	{
		if (error_reporting() == 0) {
			return;
		}

		// Route error to exception handler
		$this->exceptionHandler(new \ErrorException($message, $code, 0, $file, $line));
	}

	public function exceptionHandler($exception)
	{
		\call_user_func($this->callback, $exception->getFile(), $exception->getLine(), $exception->getMessage());
	}

	public function shutdownHandler()
	{
		if (($error = error_get_last()) === null) {
			return;
		}

		// Route error to exception handler
		$this->exceptionHandler(new \ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));
	}
}
