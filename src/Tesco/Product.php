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

	public function getUrl() {
		return \Chakula\Tesco::BASE_URL . $this->uri;
	}

	public function getInfo($title = null) {
		return \Katu\Utils\Cache::getFromMemory(['chakula', 'tesco', 'product', 'info', $this->id], function($title) {

			$src = \Katu\Utils\Cache::getUrl($this->getUrl(), 86400 * 7);
			$dom = \Katu\Utils\DOM::crawlHtml($src);

			$info = $dom->filter('.brand-bank--brand-info .groupItem')->each(function($e) {
				return ProductInfo::createFromWebsite($e);
			});

			if ($title) {
				foreach ($info as $i) {
					if ($i->title == $title) {
						return $i;
					}
				}

				return false;
			}

			return $info;

		}, 86400 * 7, $title);
	}

}
