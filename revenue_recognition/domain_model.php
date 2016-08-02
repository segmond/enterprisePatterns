<?php

/**
 * Imagine a company that sells three kinds of products: word processors, databases, and spreadsheets. 
 * According to the rules, when you sign a contract for a word processor you can book all the revenue right away. 
 * If it's a spreadsheet, you can book one-third today, one-third in sixty days, and one-third in ninety days. 
 * If it's a database, you can book one-third today, one-third in thirty days, and one-third in sixty days.
 */

class RevenueRecognition {
	private $amount;
	private $date;

	public function __construct($amount, $date) {
		$this->amount = $amount;
		$this->date = $date;
	}
	public function getAmount() { return $this->amount; }
	public function isRecognizeableBy($date_as_of) {
		return $this->date;
		return ($this->date > $date_as_of || $this->date == $date_as_of); // would be actual date compare function
	}
}

class Contract {
	private $revenue_recognitions = array();
	private $product;
	private $revenue;
	private $signed_date;
	private $id;

	public function __construct($product, $revenue, $signed_date) {
		$this->product = $product;
		$this->revenue = $revenue;
		$this->signed_date = $signed_date;
	}
	public function getRevenue() { return $this->revenue; }
	public function getWhenSigned() { return $this->signed_date; }
	public function recognized_revenue($date_as_of) {
		$revenue = 0;
		foreach ($revenue_recognitions as $r) {
			if ($r->isRecognizeableBy($date_as_of)) {
				$revenue += $r->getAmount();
			}
		}
		return $revenue;
	}
	public function addRevenueRecognition(RevenueRecognition $revenue_recognition) {
		$this->revenue_recognitions[] = $revenue_recognition;
	}
	public function calculateRecognitions() {
		$this->product->calculateRevenueRecognitions($this);
	}
	public function dumpRR() { 
		$this->id = 1; // would be coming from DB
		foreach ($this->revenue_recognitions as $r) {
			$allocation = $r->getAmount();
			$recognized_on = $r->isRecognizeableBy($this->signed_date);
			echo "INSERT into revenue_recognitions ('contract_id', 'amount', 'recognized_on') VALUES ($this->id, $allocation, $recognized_on)\n";	
			$this->id++;
		}
	}
}

class Product {
	private $name;
	private $recognition_strategy;

	public function __construct($name, $recognition_strategy) {
		$this->name = $name;
		$this->recognition_strategy = $recognition_strategy;
	}

	public static function newWordProcessor($name) {
		return new Product($name, new CompleteRecognitionStrategy());
	}
	public static function newSpreadSheet($name) {
		return new Product($name, new ThreeWayRecognitionStrategy('+ 60 DAYS', '+ 90 DAYS'));
	}
	public static function newDatabase($name) {
		return new Product($name, new ThreeWayRecognitionStrategy('+ 30 DAYS', '+ 60 DAYS'));
	}
	public function calculateRevenueRecognitions(Contract $contract) {
		$this->recognition_strategy->calculateRevenueRecognitions($contract);
	}
}

abstract class RecognitionStrategy {
	abstract function calculateRevenueRecognitions(Contract $contract);
}

class CompleteRecognitionStrategy {
	public function calculateRevenueRecognitions(Contract $contract) {
		$contract->addRevenueRecognition(new RevenueRecognition($contract->getRevenue(), $contract->getWhenSigned()));
	}
}

class ThreeWayRecognitionStrategy {
	private $first_recognition_offset;
	private $second_recognition_offset;
	public function __construct($first_offset, $second_offset) {
		$this->first_recognition_offset = $first_offset;
		$this->second_recognition_offset = $second_offset;
	}
	public function calculateRevenueRecognitions(Contract $contract) {
		$allocation = $contract->getRevenue() / 3;
		$contract->addRevenueRecognition(new RevenueRecognition($allocation, $contract->getWhenSigned()));
		$contract->addRevenueRecognition(new RevenueRecognition($allocation, $contract->getWhenSigned() . $this->first_recognition_offset));
		$contract->addRevenueRecognition(new RevenueRecognition($allocation, $contract->getWhenSigned() . $this->second_recognition_offset));
	}
}

$word = Product::newWordProcessor('thinking word');
$calc = Product::newSpreadSheet('thinking calc');
$db = Product::newDatabase('thinking db');

$calc_contract = new Contract('spreadsheet', 900, '8/1/2016');
$calc->calculateRevenueRecognitions($calc_contract);
$calc_contract->dumpRR();
