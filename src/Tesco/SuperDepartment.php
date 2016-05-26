<?php

namespace Chakula\Tesco;

class SuperDepartment {

	static function createFromWebsite($res) {
		$object = new static;
		$object->uri = $res->url;
		$object->name = preg_replace('#\s+#', ' ', $res->name);
		$object->departments = [];
		foreach ($res->items as $i) {
			$object->departments[] = Department::createFromWebsite($i);
		}

		return $object;
	}

}
