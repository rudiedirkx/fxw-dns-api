<?php

namespace rdx\fxwdns;

class Domain {

	public $id;
	public $domain;
	public $direct;
	public $records = [];

	public function __construct( $id, $domain, $direct ) {
		$this->id = $id;
		$this->domain = $domain;
		$this->direct = $direct;
	}

}
