<?php

namespace Chakula\Tesco;

class SuperDepartment {

	public $departments = [];

	public function __construct($uri, $name) {
		$this->uri = $uri;
		$this->name = $name;
	}

}
