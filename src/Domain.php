<?php

namespace rdx\fxwdns;

class Domain {

	public $id;
	public $objectId;
	public $dnsDomainId;
	public $name;
	public $direct;
	public $records = [];

	public function __construct( $id, $name, $direct ) {
		$this->id = $id;
		$this->name = strtolower($name);
		$this->direct = $direct;
	}

}
