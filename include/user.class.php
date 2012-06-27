<?php
class User{

	private $logged_in = false;
	public $username = '';

	public function __construct($username = false, $password = false){
		global $users;
		if($username){
			if($users[$username] == $this->hash_password($password)){
				$this->logged_in = true;
				$this->username = $username;
				$this->set_user_cookie();
			}
		}else{
			$this->check_user_cookie();
		}
	}

	public function log_out(){
		$username = '';
		$logged_in = false;
		setcookie('md-wiki-auth-cookie', '', time() - 3600);
		header('Location: '.URL);
	}

	public function hash_password($password){
		return crypt($password, '$2a$13$dV9cf433ed343h476C55e8$');
	}

	public function is_logged_in(){
		return $this->logged_in;
	}

	public function set_user_cookie(){
		global $users;
		$cookie = $this->username.':';
		$hash = $users[$this->username];
		for($i = 0; $i < 20; $i++){
			$hash = md5($hash);
		}
		$cookie .= $hash;
		setcookie('md-wiki-auth-cookie', $cookie);
	}

	public function check_user_cookie(){
		global $users;

		if(!isset($_COOKIE['md-wiki-auth-cookie']))
			return;

		$cookie = explode(':', $_COOKIE['md-wiki-auth-cookie']);
		$hash = $users[$cookie[0]];

		for($i = 0; $i < 20; $i++){
			$hash = md5($hash);
		}

		if($cookie[1] == $hash){
			$this->logged_in = true;
			$this->username = $cookie[0];
		}
	}

}