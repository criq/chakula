<?php

namespace Chakula;

class Tesco {

	const BASE_URL = 'https://nakup.itesco.cz';

	static function getDepartmentTree() {
		$url = static::BASE_URL;
		$src = \Katu\Utils\Cache::getUrl($url . '/groceries/cs-CZ/', 86400);
		$dom = \Katu\Utils\DOM::crawlHtml($src);

		$superDepartments = [];

		$array = json_decode($dom->filter('html')->attr('data-redux-state'));

		foreach (json_decode($dom->filter('html')->attr('data-redux-state'))[array_search('taxonomy', $array) + 1][2] as $superDepartmentArray) {
			$superDepartment = new Tesco\SuperDepartment($superDepartmentArray[8], $superDepartmentArray[2]);
			foreach (array_slice($superDepartmentArray[6], 1) as $departmentArray) {
				$department = new Tesco\Department($departmentArray[8], $departmentArray[2]);
				foreach (array_slice($departmentArray[6], 1) as $categoryArray) {
					$category = new Tesco\Category($categoryArray[6], $categoryArray[2]);
					$department->categories[] = $category;
				}
				$superDepartment->departments[] = $department;
			}
			$superDepartments[] = $superDepartment;
		}

		return $superDepartments;
	}

}
