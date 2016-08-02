<?php

// DB connection
require_once('dbconf.php');
class DB 
{
    private static $instance = null;
    private $db = null;

    private function __construct() { }

    public static function getInstance() {
        if (self::$instance == null) { self::$instance = new DB(); }
        return self::$instance;
    }

    public function getDB() {
        if ($this->db != null) { return $this->db; }
        try {
            $this->db = new \PDO('pgsql:host='.DBHOST.' dbname='.DBNAME.' user='.DBUSER.' password='.DBPASS); // define these in your dbconf.php
            $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\Exception $e) { throw new \Exception("Could not open DB connection {$e->getMessage()}."); }   
        return $this->db;
    }
}

/**
 * Imagine a company that sells three kinds of products: word processors, databases, and spreadsheets. 
 * According to the rules, when you sign a contract for a word processor you can book all the revenue right away. 
 * If it's a spreadsheet, you can book one-third today, one-third in sixty days, and one-third in ninety days. 
 * If it's a database, you can book one-third today, one-third in thirty days, and one-third in sixty days.
 */

// Product table data gateway
class ProductTDG {
    public function find($id) {
    }
    public function findName($id) {
    }
    public function update($id, $name, $type) {
    }
    public function insert($name, $type) {
    }
    public function delete($id) {
    }
}

// Contracts Table data gateway
class ContractsTDG {
    public function findContract($contract_id) {
        switch ($contract_id) {
            case 1:
                return array('revenue'=>900, 'type'=>'spreadsheet', 'date_signed'=>'8/1/2016');
            case 2:
                return array('revenue'=>900, 'type'=>'word processor', 'date_signed'=>'8/1/2016');
            case 3:
                return array('revenue'=>900, 'type'=>'database', 'date_signed'=>'8/1/2016');
            default:
                throw new Exception("Could not find record");
        }
    }

    public function insert($product_type, $amount) {
            echo "INSERT INTO $product_type, $amount, now() RETURNING ID\n";
    }
}

// Revenue Recogntion Table data gateway
class RevenueRecognitionTDG {
    private $db;
    public function findRecognitionsFor($contract_id, $date_as_of) {
        $find_recognitions_query = 'SELECT amount FROM revenue_recognitions WHERE contract = :contract AND recognized_on <= :recognized_on';
        $db = DB::getInstance()->getDB();
        $find_recognitions_stmt = $db->prepare($find_recognitions_query);
        $params = array(
            ':contract'=>$contract_id,
            ':recognized_on'=>$date_as_of
        );
        return $find_recognitions_stmt->execute($params);
    }

    public function insertRecognition($contract_num, $allocation, $recognized_on) {
        echo "INSERT INTO ep_demo.revenue_recognitions ('contract', 'amount', 'recognized_on) VALUES ($contract_num, $allocation, $recognized_on\n";
    }
}

// Recognition Service accesses our data using our Table Data Gateways and uses transaction script to organize our business logic
class RecognitionService {
    public function __construct() {
        $this->rr_tdg = new RevenueRecognitionTDG();
        $this->c_tdg = new ContractsTDG();
    }

    public function recognizedRevenue($contract_num, $date_as_of) {
        $db_stmt = $this->rr_tdg->findRecognitionsFor($contract_num, $date_as_of);
        $result = 0.0;
        while ($row = $db_stmt->fetchAll(PDO::FETCH_NAMED)) {
            $result =+ $row['amount'];
        }
        return $result;
    }

    // Transaction script 
    public function calculateRevenueRecognitions($contract_num) {
        $contracts = $this->c_tdg->findContract($contract_num);
        $total_revenue = $contracts['revenue'];
        $date_signed = $contracts['date_signed'];
        $contract_type = $contracts['type'];
        $allocation = $total_revenue/3;
        if ($contract_type == 'spreadsheet') {
            //$this->rr_tdg->insertRecognition($contract_num, $allocation[0], $date_signed);
            $this->rr_tdg->insertRecognition($contract_num, $allocation, $date_signed);
            $this->rr_tdg->insertRecognition($contract_num, $allocation, $date_signed . ' + 60 DAYS');
            $this->rr_tdg->insertRecognition($contract_num, $allocation, $date_signed . ' + 90 DAYS');
        } else if ($contract_type == 'word processor') {
            $this->rr_tdg->insertRecognition($contract_num, $total_revenue, $date_signed);
        } else if ($contract_type == 'database') {
            $this->rr_tdg->insertRecognition($contract_num, $allocation, $date_signed);
            $this->rr_tdg->insertRecognition($contract_num, $allocation, $date_signed . ' + 30 DAYS');
            $this->rr_tdg->insertRecognition($contract_num, $allocation, $date_signed . ' + 60 DAYS');
        }
    }
}

$db = DB::getInstance();
assert($db instanceof DB == true);

$rs = new RecognitionService();
$rs->calculateRevenueRecognitions(1);
echo "\n";
$rs->calculateRevenueRecognitions(2);
echo "\n";
$rs->calculateRevenueRecognitions(3);
