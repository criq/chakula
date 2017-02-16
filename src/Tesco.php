<?php

namespace Chakula;

class Tesco {

	const BASE_URL = 'https://nakup.itesco.cz';

	static function getDepartmentTree() {
		$url = static::BASE_URL;
		$src = \Katu\Utils\Cache::getUrl($url . '/groceries/cs-CZ/', 86400);
		$dom = \Katu\Utils\DOM::crawlHtml($src);

		$superDepartments = [];

		foreach (json_decode($dom->filter('html')->attr('data-redux-state'))[10][2] as $superDepartmentArray) {
			$superDepartment = new Tesco\SuperDepartment($superDepartmentArray[2], $superDepartmentArray[4]);
			foreach (array_slice($superDepartmentArray[10], 1) as $departmentArray) {
				$department = new Tesco\Department($departmentArray[2], $departmentArray[4]);
				foreach (array_slice($departmentArray[10], 1) as $categoryArray) {
					$category = new Tesco\Category($categoryArray[2], $categoryArray[4]);
					$department->categories[] = $category;
				}
				$superDepartment->departments[] = $department;
			}
			$superDepartments[] = $superDepartment;
		}

		return $superDepartments;
	}

}
