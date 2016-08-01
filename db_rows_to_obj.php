<?php


class DBDerivedObject {
    public function __construct($db_row) {
        foreach ($this->required_fields as $field) {
            if (!isset($db_row[$field])) {
                throw new Exception("Could not construct ".__CLASS__." object, due to missing '$field' in constructor record");
            }
        }
    }
}
class Person extends DBDerivedObject {
    protected $required_fields = array('first_name', 'age', 'city');
    private $first_name;
    private $age;
    private $city;

    public function __construct($db_row) {
        parent::__construct($db_row);
        $this->first_name = $db_row['first_name'];
        $this->age = $db_row['age'];
        $this->city = $db_row['city'];
    }

    public function getFirstName() { return $this->first_name; }
    public function getAge() { return $this->age; }
    public function getCity() { return $this->city; }

    public function isFromDetroit() {
        return $this->city == 'detroit';
    }
}

$db_rows = array('first_name'=>'jane', 'age'=>25, 'city'=>'las vegas');
$p = new Person($db_rows);
echo "Firstname is ", $p->getFirstName() . "\n";
echo "Age is ", $p->getAge() . "\n";
echo "City is ", $p->getCity() . "\n";
$from_detroit = $p->isFromDetroit() ? 'TRUE' : 'FALSE';
echo "Is from detroit $from_detroit\n"; 


/*
$bad_record = array('first_name'=>'smith', 'dob'=>'1974');
$g = new Person($bad_record);
echo "Firstname is ", $p->getFirstName() . "\n";
echo "Age is ", $p->getAge() . "\n";
echo "City is ", $p->getCity() . "\n";
 */


