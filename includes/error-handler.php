<?php
set_exception_handler('exceptionHandler');

set_error_handler(function($number, $message, $file, $line){
	exceptionHandler(new ErrorException($message, $number, 0, $file, $line));
});

register_shutdown_function(function(){
	if($error = error_get_last())
		exceptionHandler(new ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));
});

function exceptionHandler($e){
	if(error_reporting() == 0)
		return;

	file_put_contents(LOGS . 'error-' . date('Ymd') . '_' . md5(microtime()) . '.log', implode("\n", array(
		'Date	:' . date('Y-m-d H:i:s'),
		'Error	: '	. $e->getMessage(),
		'File	: '	. $e->getFile() . ': ' . $e->getLine(),
		'URL	: '	. ((isset($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : ''),
	)));

	switch($e->getCode()){
		case E_ERROR:
		case E_PARSE:
		case E_CORE_ERROR:
		case E_CORE_WARNING:
		case E_COMPILE_ERROR:
		case E_COMPILE_WARNING:
		case E_USER_ERROR:
			die('<html><head><title>Internal Server Error</title></head><body><h2>Internal Server Error</h2><p>An unexpected error has occurred.</p></body></html>');

		case E_WARNING:
		case E_NOTICE:
		case E_USER_WARNING:
		case E_USER_NOTICE:
		case E_STRICT:
		case E_RECOVERABLE_ERROR:
		case E_DEPRECATED:
		case E_USER_DEPRECATED:
		default:

		break;
	}
}
?>