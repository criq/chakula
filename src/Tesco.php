<?php

namespace Chakula;

class Tesco {

	const BASE_URL = 'https://nakup.itesco.cz/groceries/cs-CZ/';

	public function getSuperDepartments() {
		$url = static::BASE_URL;
		$src = \Katu\Utils\Cache::getUrl($url, 86400);

		echo $src; die;

		preg_match('#data-props="(.+)"#U', $src, $match);
		$res = \Katu\Utils\JSON::decodeAsObjects(html_entity_decode($match[1]));

		$departments = array_filter($res->nav, function($i) {
			return !in_array($i->name, \Katu\Config::get('tesco', 'departments', 'exclude'));
		});

		$categoryIds = [];
		foreach ($departments as $department) {
			foreach ($department->items as $item) {
				foreach ($item->items as $category) {
					$categoryIds[] = $category->catId;
				}
			}
		}

		return $categoryIds;
	}

}
