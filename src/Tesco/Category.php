<?php

namespace Chakula\Tesco;

class Category {

	static function createFromWebsite($res) {
		$object = new static;
		$object->id = $res->catId;
		$object->uri = $res->url;
		$object->name = $res->name;

		return $object;
	}

	public function getUrl() {
		return \Chakula\Tesco::BASE_URL . 'categories/' . $this->id;
	}

	public function getPages() {
		try {

			$src = \Katu\Utils\Cache::getUrl($this->getUrl(), 86400);
			$dom = \Katu\Utils\DOM::crawlHtml($src);

			preg_match('#([0-9]+) položek$#', $dom->filter('.results-count')->text(), $match);
			return (int) ceil($match[1] / 24);

		} catch (\Exception $e) {
			return 1;
		}
	}

	public function getProducts() {
		$products = [];
		for ($page = 1; $page <= $this->getPages(); $page++) {

			try {

				$url = \Katu\Types\TUrl::make($this->getUrl(), [
					'page' => $page > 1 ? $page : null,
				]);
				$src = \Katu\Utils\Cache::getUrl($url, 86400);
				$dom = \Katu\Utils\DOM::crawlHtml($src);

				$products = array_merge($products, $dom->filter('.product-lists .product-list--list-item')->each(function($e) {
					return Product::createFromWebsite($e);
				}));

			} catch (\Exception $e) {
				var_dump($e); die;
				/* Nevermind. */
			}

		}

		return $products;
	}

}
