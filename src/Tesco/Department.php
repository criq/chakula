<?php

namespace Chakula\Tesco;

class Department {

	public $categories = [];

	public function __construct($id, $name) {
		$this->id = $id;
		$this->name = $name;
	}

}
