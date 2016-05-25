<?php

namespace Chakula\Tesco;

class ProductInfo {

	static function createFromWebsite($dom) {
		$object = new static;
		$object->title = $dom->filter('h3')->text();
		$object->text = trim(preg_replace('#<h3.+</h3>#', null, $dom->html()));

		return $object;
	}

}
