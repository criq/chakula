<?php

namespace Chakula;

class Tesco {

	const BASE_URL = 'https://nakup.itesco.cz';

	static function getDepartmentTree() {
		$url = static::BASE_URL;
		$src = \Katu\Utils\Cache::getUrl($url . '/groceries/cs-CZ/', 86400);
		$dom = \Katu\Utils\DOM::crawlHtml($src);

		$superDepartments = $dom->filter('.menu-superdepartment .navigation-menu--link')->each(function($i) {
			$superDepartments[] = Tesco\SuperDepartment::createFromWebsite($i);
		});

		return $superDepartments;
	}

}
