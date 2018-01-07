<?php

namespace rdx\fxwdns;

class Customer {

	public $id;
	public $name;

	public function __construct( $id, $name ) {
		$this->id = $id;
		$this->name = $name;
	}

}
