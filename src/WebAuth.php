<?php

namespace rdx\fxwdns;

use GuzzleHttp\Cookie\CookieJar;

class WebAuth {

	public $cookies;
	public $user = '';
	public $pass = '';

	/**
	 * Dependency constructor
	 */
	public function __construct( $user, $pass ) {
		$this->cookies = $this->getCookieJar();
		$this->user = $user;
		$this->pass = $pass;
	}

	/**
	 *
	 */
	protected function getCookieJar() {
		return new CookieJar;
	}

}
