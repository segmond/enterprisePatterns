<?php

/**
 * Imagine a company that sells three kinds of products: word processors, databases, and spreadsheets. 
 * According to the rules, when you sign a contract for a word processor you can book all the revenue right away. 
 * If it's a spreadsheet, you can book one-third today, one-third in sixty days, and one-third in ninety days. 
 * If it's a database, you can book one-third today, one-third in thirty days, and one-third in sixty days.
 */

class TableModule {
    protected $table_data;
    protected $table_name;
    public function __construct($table_name, $table_data) {
        $this->table_name = $table_name;
        $this->table_data = $table_data;
    }
}

class Contract extends TableModule {
    public function __construct($table_data) {
        parent::__construct("ep_demo.contracts", $table_data);
    }

    public function getDataRow($key) {
        foreach ($this->table_data[$this->table_name] as $row) {
            if ($key == $row['id']) { return $row; }
        }
        return array();
        //return $this->table_data[$this->table_name][$key]; // we can use array filter with php 5.6 >
    }

    public function getProductId($contract_id) {
        foreach ($this->table_data[$this->table_name] as $row) {
            if ($contract_id == $row['id']) { return $row['product_id']; }
        }
        return null;
    }

    public function getWhenSigned($contract_id) {
        foreach ($this->table_data[$this->table_name] as $row) {
            if ($contract_id == $row['id']) { return $row['date_signed']; }
        }
        return null;
    }

    public function calculateRecognitions($contract_id) {
        $contract_row = $this->getDataRow($contract_id);
        $amount = $contract_row['revenue'];
        $rr = new RevenueRecognition($this->table_data);
        $product = new Product($this->table_data);
        $product_id = $this->getProductId($contract_id);

        if ($product->getProductType($product_id) == Product::WP) {
            $rr->insert($contract_id, $amount, $this->getWhenSigned($contract_id));
        } else if ($product->getProductType($product_id) == Product::SS) {
            $allocation = $amount/3;
            $rr->insert($contract_id, $allocation, $this->getWhenSigned($contract_id));
            $rr->insert($contract_id, $allocation, $this->getWhenSigned($contract_id) . ' + 60 DAYS');
            $rr->insert($contract_id, $allocation, $this->getWhenSigned($contract_id) . ' + 90 DAYS');
        } else if ($product->getProductType($product_id) == Product::DB) {
            $allocation = $amount/3;
            $rr->insert($contract_id, $allocation, $this->getWhenSigned($contract_id));
            $rr->insert($contract_id, $allocation, $this->getWhenSigned($contract_id) . ' + 30 DAYS');
            $rr->insert($contract_id, $allocation, $this->getWhenSigned($contract_id) . ' + 60 DAYS');
        }
    }

}

class Product extends TableModule {
    const SS = "SS";
    const WP = "WP";
    const DB = "DB";

    public function __construct($table_data) {
        parent::__construct("ep_demo.products", $table_data);
    }

    public function getProductType($product_id) {
        foreach ($this->table_data[$this->table_name] as $row) {
            if ($row['id'] == $product_id) {
                return $row['type'];
            }
        }
        throw new Exception("Unknown Product Type");
    }
}

class RevenueRecognition {
    public function insert($contract_id, $amount, $bill_date) {
        echo "INSERT INTO ep_demo.revenue_recognitions (contract, amount, recognized_on) VALUES ($contract_id, $amount, $bill_date)\n"; // return $id;
    }
}

$word = 'wordprocessor';
$calc = 'spreadsheet';
$db = 'database';

$data_set = array(
    'ep_demo.contracts'=>
        array(
            array('id'=>1, 'product_id'=>2, 'revenue'=>300, 'date_signed'=>'7/25/2016'),
            array('id'=>2, 'product_id'=>3, 'revenue'=>780, 'date_signed'=>'7/25/2016'),
            array('id'=>3, 'product_id'=>1, 'revenue'=>420, 'date_signed'=>'7/25/2016'),
        ),
    'ep_demo.products'=>
        array(
            array('id'=>1, 'name'=>'spreadsheet', 'type'=>'SS'),
            array('id'=>2, 'name'=>'wordprocessor', 'type'=>'WP'),
            array('id'=>3, 'name'=>'database', 'type'=>'DB'),
        )
);
$calc_contract = new Contract($data_set);
$contract_id = 3;
$calc_contract->calculateRecognitions($contract_id);
