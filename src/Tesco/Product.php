<?php

namespace Chakula\Tesco;

class Product {

	static function createFromWebsite($dom) {
		$object = new static;
		preg_match('#/groceries/cs-CZ/products/([0-9]+)#', $dom->filter('.product-tile--title a')->attr('href'), $match);
		$object->id = $match[1];
		$object->uri = $dom->filter('.product-tile--title a')->attr('href');
		$object->name = $dom->filter('.product-tile--title a')->text();

		return $object;
	}

}
