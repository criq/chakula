<?php

namespace Chakula;

class Tesco {

	const BASE_URL = 'https://nakup.itesco.cz/groceries/cs-CZ/';

	static function getDepartmentTree() {
		$url = static::BASE_URL;
		$src = \Katu\Utils\Cache::getUrl($url, 86400);

		preg_match('#data-props="(.+)"#U', $src, $match);
		$res = \Katu\Utils\JSON::decodeAsObjects(html_entity_decode($match[1]));

		$superDepartments = [];
		foreach ($res->nav as $i) {
			$superDepartments[] = Tesco\SuperDepartment::createFromWebsite($i);
		}

		return $superDepartments;
	}

}
