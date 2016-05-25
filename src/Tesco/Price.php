<?php

namespace Chakula\Tesco;

class Price {

	public function __construct($amount, $currency = 'CZK') {
		$this->amount = (new \Katu\Types\TString($amount))->getAsFloat();
		switch ($currency) {
			case 'Kč' : $this->currency = 'CZK'; break;
			default : $this->currency = $currency; break;
		}
	}

}
