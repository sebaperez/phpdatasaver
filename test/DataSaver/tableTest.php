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

	public function test_update() {

		$table = \DataSaver\Table::model([
			"db" => "test",
			"name" => "test_update",
			"columns" => [
				[ "name" => "date", "type" => "datetime" ],
				[ "name" => "item", "type" => "int" ],
				[ "name" => "value", "type" => "int" ]
			],
			"keys" => [ "date", "item" ]
		]);

		$this->assertNotFalse($table);

		$date = date("Y-m-d");
		$value0 = rand(10, 100);
		$value1 = rand(10, 100);
		$value2 = rand(10, 100);

		$table->insertOrUpdate([ "value" => $value0 ], [ "date" => $date, "item" => 1 ]);
		$table->insertOrUpdate([ "value" => $value1 ], [ "date" => $date, "item" => 1 ]);
		$table->insertOrUpdate([ "value" => $value2 ], [ "date" => $date, "item" => 2 ]);

		$result1 = $table->select("value where date = '$date' and item = 1");
		$result2 = $table->select("value where date = '$date' and item = 2");
		$this->assertEquals($value1, $result1[0]["value"]);
		$this->assertEquals($value2, $result2[0]["value"]);
	}

	public function test_column_types() {
		$TYPES_EXPECTED = [
			"s" => [ "char", "varchar(50)", "text", "tinytext", "mediumtext", "longtext", "datetime", "date", "timestamp" ],
			"b" => [ "blob", "tinyblob", "mediumblob", "longblob" ],
			"i" => [ "tinyint", "smallint", "int", "mediumint", "bigint" ],
			"d" => [ "float", "double" ]
		];

		$COLUMNS = [];
		foreach ($TYPES_EXPECTED as $symbol => $types) {
			for ($i = 0; $i < count($types); $i++) {
				$type = $types[$i];
				$name = $symbol . "_" . $i;
				array_push($COLUMNS, [ "name" => $name, "type" => $type ]);
			}
		}

		$table = \DataSaver\Table::model([
			"db" => "test",
			"name" => "test",
			"columns" => $COLUMNS
		]);

		$this->assertNotFalse($table);

		foreach ($TYPES_EXPECTED as $symbol => $types) {
			for ($i = 0; $i < count($types); $i++) {
				$name = $symbol . "_" . $i;
				$this->assertEquals($symbol, $table->getTypeSymbolForColumn($name), "Expected $symbol for " . $types[$i] . " but failed");
			}
		}
	}

}

?>
