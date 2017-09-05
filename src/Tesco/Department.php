<?php

namespace Chakula\Tesco;

class Department {

	public $categories = [];

	public function __construct($uri, $name) {
		$this->uri = $uri;
		$this->name = $name;
	}

}
