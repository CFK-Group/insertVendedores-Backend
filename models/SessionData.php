<?php

namespace app\models;

class SessionData {
	
	const LOGIN_ERROR = -1;
	const LOGIN_OK = 0;
	const LOGIN_NO_USER = -2;
	const WRONG_PASS = -3;
	const WRONG_DEVICE = -4;
	const NO_AUTH = -5;

	public $sessionToken;
	public $statusCode;
	public $errorDesc;
	
	function __construct($statusCode, $errorDesc, $session){
		$this->sessionToken = $session;
		$this->errorDesc = $errorDesc;
		$this->statusCode = $statusCode;
		return $this;
	}
	
}