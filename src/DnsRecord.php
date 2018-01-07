<?php

namespace rdx\fxwdns;

class DnsRecord {

	public $id;
	public $name;
	public $type;
	public $value;
	public $prio;
	public $ttl;

	public function __construct( $id, $name, $type, $value, $prio, $ttl ) {
		$this->id = $id;
		$this->name = $name;
		$this->type = $type;
		$this->value = $value;
		$this->prio = $prio;
		$this->ttl = $ttl;
	}

}
