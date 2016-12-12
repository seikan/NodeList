<?php
class Session {
	protected $session, $id;

	public function __construct($id = NULL) {
		if(!session_id())
			session_start();

		$this->id = ($id) ? $id : md5($_SERVER['HTTP_HOST'] . '_session');

		if(isset($_SESSION[$this->id]) && is_null($this->session = json_decode($_SESSION[$this->id], TRUE)) === FALSE)
			return;

		$_SESSION[$this->id] = json_encode(array(''=>''));
		$this->session = json_decode($_SESSION[$this->id], TRUE);
	}

	public function get($key) {
		return (isset($this->session[$key])) ? $this->session[$key] : NULL;
	}

	public function set($key, $value = NULL) {
		$this->session[$key] = $value;

		if(is_null($value))
			unset($this->session[$key]);

		$_SESSION[$this->id] = json_encode($this->session);
	}

	public function destroy() {
		unset($_SESSION[$this->id]);
		$this->session = NULL;
	}
}
?>