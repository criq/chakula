<?php

namespace Chakula\CountryLife;

class Item extends \Katu\Model {

	const TABLE = 'chakula_countrylife_items';

	const BASE_URL = 'https://www.countrylife.cz';
	const TIMEOUT = 1209600;

	static function createTable() {
		$sql = " CREATE TABLE `" . static::TABLE . "` (
				`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				`timeCreated` datetime NOT NULL,
				`timeScraped` datetime DEFAULT NULL,
				`uri` varchar(255) NOT NULL DEFAULT '',
				`reference` varchar(255) DEFAULT NULL,
				`reference` varchar(255) DEFAULT NULL,
				`name` varchar(255) DEFAULT NULL,
				`category` text,
				`details` text,
				`nutrients` text,
				PRIMARY KEY (`id`),
				KEY `uri` (`uri`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 ";

		try {
			$result = static::getPdo()->createQuery($sql)->getResult();
		} catch (\Exception $e) {
			if ($e->getCode() == 1050) {
				/* Table exists, nevermind. */
			} else {
				throw $e;
			}
		}

		return true;
	}

	static function buildDatabase() {
		// Make sure we have a table.
		static::createTable();

		for ($page = 1; $page <= static::getPages(); $page++) {

			$src = \Katu\Utils\Cache::getUrl(\Katu\Types\TUrl::make(static::BASE_URL . '/biopotraviny', [
				'page' => $page,
			]), static::TIMEOUT);

			$dom = \Katu\Utils\DOM::crawlHtml($src);
			$dom->filter('.product-list .item')->each(function($i) {

				static::upsert([
					'uri' => $i->filter('a.top')->attr('href'),
				], [
					'timeCreated' => new \Katu\Utils\DateTime,
				]);

			});

		}
	}

	static function getPages() {
		$src = \Katu\Utils\Cache::getUrl(static::BASE_URL . '/biopotraviny', static::TIMEOUT);
		$dom = \Katu\Utils\DOM::crawlHtml($src);

		return (int) $dom->filter('.col-content .pager a')->last()->html();
	}

	/****************************************************************************/

	public function getUrl() {
		return static::BASE_URL . $this->uri;
	}

	public function getSrc() {
		return \Katu\Utils\Cache::getUrl($this->getUrl());
	}

	public function getDOM() {
		return \Katu\Utils\DOM::crawlHtml($this->getSrc());
	}

	public function scrape() {
		$dom = $this->getDOM();

		// Reference.
		$node = $dom->filter('#frm-productInfo-buyBoxForm [name="productItem"]');
		if ($node->count()) {
			$this->update('reference', $node->attr('value'));
		} else {
			$node = $dom->filter('.icon-to-list');
			if ($node->count()) {
				$url = new \Katu\Types\TUrl(static::BASE_URL . $node->attr('href'));
				$this->update('reference', $url->getQueryParam('product'));
			}
		}

		// Name.
		$this->update('name', trim($dom->filter('.product-cols h1')->html()));

		// Category.
		$category = $dom->filter('#breadcrumb a')->each(function($e) {
			return trim($e->html());
		});
		$this->update('category', \Katu\Utils\JSON::encode($category));

		// Details.
		$details = [];
		foreach (array_filter(array_map('trim', explode('<h3>', $dom->filter('#content')->html()))) as $line) {
			list($title, $content) = explode('</h3>', $line);
			$details[] = [
				'title' => trim($title),
				'value' => trim(strip_tags($content)),
			];
		}
		$this->update('details', \Katu\Utils\JSON::encode($details));

		// Nutrients.
		$nutrients = [];
		$node = $dom->filter('.table-content');
		if ($node->count()) {

			// Energy.
			if (preg_match('/Energetická hodnota ([0-9\s]+) kJ/', $node->filter('.big')->html(), $match)) {
				$nutrients['energy'] = [
					'amount' => (new \Katu\Types\TString($match[1]))->getAsFloat(),
					'unit' => 'kJ',
				];
			}

			$tableNutrients = $node->filter('tr')->each(function($e) {
				if ($e->filter('th')->count() && $e->filter('td')->count()) {

					$nutrientCode = null;
					$nutrientName = trim(strip_tags($e->filter('th')->html()));
					$nutrientAmount = trim(preg_replace('/\s+/', ' ', preg_replace('/\v/', ' ', strip_tags($e->filter('td')->html()))));

					switch ($nutrientName) {
						case 'Tuky:' :
							$nutrientCode = 'fats';
						break;
						case 'z toho nasycené mastné kyseliny:' :
							$nutrientCode = 'saturatedFattyAcids';
						break;
						case 'Sacharidy:' :
							$nutrientCode = 'carbs';
						break;
						case 'z toho cukry:' :
							$nutrientCode = 'sugar';
						break;
						case 'Bílkoviny:' :
							$nutrientCode = 'proteins';
						break;
						case 'Sůl' :
							$nutrientCode = 'salt';
						break;
					}

					if ($nutrientCode && preg_match('/(?<amount>[0-9,]+)\s+(?<unit>\pL+)/ui', $nutrientAmount, $match)) {
						return [
							'code' => $nutrientCode,
							'amount' => (new \Katu\Types\TString($match['amount']))->getAsFloat(),
							'unit' => $match['unit'],
						];
					}

				}
			});

			foreach (array_values(array_filter($tableNutrients)) as $tableNutrient) {
				$nutrients[$tableNutrient['code']] = [
					'amount' => $tableNutrient['amount'],
					'unit' => $tableNutrient['unit'],
				];
			}

		}
		$this->update('nutrients', \Katu\Utils\JSON::encode($nutrients));

		$this->update('timeScraped', new \Katu\Utils\DateTime);
		$this->save();

		return true;
	}

	public function getNutrients() {
		return \Katu\Utils\JSON::decodeAsArray($this->nutrients);
	}

	public function getNutrientAmountWithUnitByCode($code) {
		$nutrients = $this->getNutrients();
		if (isset($nutrients[$code])) {
			return new \App\Classes\AmountWithUnit($nutrients[$code]['amount'], \App\Models\PracticalUnit::getOneBy([
				'abbr' => $nutrients[$code]['unit'],
			]));
		}

		return false;
	}

	public function getDetails() {
		return \Katu\Utils\JSON::decodeAsArray($this->details);
	}

	public function getDetailByTitle($title) {
		foreach ($this->getDetails() as $detail) {
			if ($detail['title'] == $title) {
				return $detail['value'];
			}
		}

		return false;
	}

}
