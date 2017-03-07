<?php

namespace Chakula\Tesco;

class Product {

	public function __construct($id = null) {
		$this->id = $id;
	}

	public function isAvailable() {
		try {
			return (bool) $this->getSrc();
		} catch (\Katu\Exceptions\ErrorException $e) {
			return false;
		}
	}

	static function createFromDom($dom) {
		$object = new static;
		preg_match('#/groceries/cs-CZ/products/([0-9]+)#', $dom->filter('a.product-tile--title')->attr('href'), $match);
		$object->id = $match[1];
		$object->uri = $dom->filter('a.product-tile--title')->attr('href');
		$object->name = $dom->filter('a.product-tile--title')->text();

		return $object;
	}

	public function getUrl() {
		if (isset($this->uri) && $this->uri) {
			return \Chakula\Tesco::BASE_URL . $this->uri;
		} else {
			return \Chakula\Tesco::BASE_URL . '/groceries/cs-CZ/products/' . $this->id;
		}
	}

	public function getSrc() {
		return \Katu\Utils\Cache::getUrl($this->getUrl(), 86400 * 7);
	}

	public function getDOM() {
		return \Katu\Utils\DOM::crawlHtml($this->getSrc());
	}

	public function getName() {
		return trim($this->getDOM()->filter('h1.product-title')->text());
	}

	public function getPrice() {
		$dom = $this->getDOM();

		$productPrice = new ProductPrice;

		$domPricePerUnit = $dom->filter('.price-per-sellable-unit');
		if ($domPricePerUnit->count()) {
			$productPrice->price = new Price($domPricePerUnit->filter('.value')->text(), $domPricePerUnit->filter('.currency')->text());
			$productPrice->regular = $productPrice->price;
		}

		$domPromoPricePerUnit = $dom->filter('.product-promotion .offer-text');
		if ($domPromoPricePerUnit->count() && preg_match('#běžná cena ([0-9,]+) nyní ([0-9,]+)#u', $domPromoPricePerUnit->text(), $match)) {
			$productPrice->regular = new Price($match[1]);
			$productPrice->promo = new Price($match[2]);
		}

		$domPricePerQuantity = $dom->filter('.price-per-quantity-weight');
		if ($domPricePerQuantity->count()) {
			$productPrice->pricePerQuantity = new PricePerQuantity(new Price($domPricePerQuantity->filter('.value')->text(), $domPricePerQuantity->filter('.currency')->text()), new Quantity(1, $domPricePerQuantity->filter('.weight')->text()));
		}

		return $productPrice;
	}

	public function getInfo($title = null) {
		return \Katu\Utils\Cache::get(['chakula', 'tesco', 'product', 'info', $this->id], function($title) {

			$src = $this->getSrc();
			$dom = \Katu\Utils\DOM::crawlHtml($src);

			$info = $dom->filter('.brand-bank--brand-info .groupItem, .brand-bank--brand-info .using-product-info')->each(function($e) {
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
