<?php

namespace Chakula\Tesco;

class SuperDepartment {

	static function createFromWebsite($dom) {
		$object = new static;
		$object->uri = $dom->attr('href');
		$object->name = $dom->text();
		$object->departments = [];
		foreach ($res->items as $i) {
			$object->departments[] = Department::createFromWebsite($i);
		}

		return $object;
	}

}
