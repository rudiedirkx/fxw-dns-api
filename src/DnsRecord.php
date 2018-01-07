<?php

namespace rdx\fxwdns;

class DnsRecord {

	public $id;
	public $name;
	public $type;
	public $value;
	public $ttl;
	public $prio;

	public function __construct( $id, $name, $type, $value, $ttl, $prio = '' ) {
		$this->id = $id;
		$this->name = strtolower($name);
		$this->type = strtoupper($type);
		$this->value = $value;
		$this->ttl = $ttl;
		$this->prio = $prio;
	}

}
