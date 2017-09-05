<?php

namespace Chakula\Tesco;

class Category {

	public function __construct($uri, $name) {
		$this->uri = $uri;
		$this->name = $name;
	}

	public function getUrl() {
		return \Chakula\Tesco::BASE_URL . '/groceries/cs-CZ/shop' . $this->uri;
	}

	public function getPages() {
		try {

			$src = \Katu\Utils\Cache::getUrl($this->getUrl(), 86400);
			$dom = \Katu\Utils\DOM::crawlHtml($src);

			preg_match('#([0-9]+) položek$#', $dom->filter('.pagination--results-count')->text(), $match);
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

				$products = array_merge($products, $dom->filter('.product-list .product-list--list-item')->each(function($e) {
					return Product::createFromDom($e);
				}));

			} catch (\Exception $e) {
				// Nevermind.
			}

		}

		return $products;
	}

}
