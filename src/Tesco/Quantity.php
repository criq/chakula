<?php

namespace Chakula\Tesco;

class Quantity {

	public function __construct($amount, $unit) {
		$this->amount = (new \Katu\Types\TString((string) $amount))->getAsFloat();
		$this->unit = ltrim($unit, '/');
	}

}
