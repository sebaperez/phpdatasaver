<?php

namespace DataSaver\Test;

use DataSaver\Table;

class tableTest extends \PHPUnit\Framework\TestCase {

	public function test_table_model() {
		$dbName = "test";
		$tableName = "test";
		$key = "test_id";
		$name = "test";
		$testName = "test1";

		$table = \DataSaver\Table::model([
			"db" => $dbName,
			"name" => $tableName,
			"columns" => [
				[ "name" => $key, "type" => "int", "options" => "auto_increment primary key" ],
				[ "name" => $name, "type" => "varchar(255)" ]
			]
		]);

		$this->assertNotFalse($table);

		$test_id = $table->insert([ "$name" => $testName ]);
		$result = $table->select("$key, $name where $key = $test_id");
		$this->assertCount(1, $result);
		$this->assertEquals($test_id, $result[0][$key]);
		$this->assertEquals($testName, $result[0][$name]);
	}

}

?>
