<?php

namespace Chakula\Tesco;

class SuperDepartment {

	public $departments = [];

	public function __construct($id, $name) {
		$this->id = $id;
		$this->name = $name;
	}

}
