<?php

namespace Chakula\Tesco;

class Department {

	static function createFromWebsite($res) {
		$object = new static;
		$object->uri = $res->url;
		$object->name = preg_replace('#\s+#', ' ', $res->name);
		$object->categories = [];
		foreach ($res->items as $i) {
			$object->categories[] = Category::createFromWebsite($i);
		}

		return $object;
	}

}
