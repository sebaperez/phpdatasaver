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
		$this->assertCount(2, array_keys($result[0]));
		$this->assertEquals($test_id, $result[0][$key]);
		$this->assertEquals($testName, $result[0][$name]);
	}

	public function test_update() {
		$table = \DataSaver\Table::model([
			"db" => "test",
			"name" => "test_update",
			"columns" => [
				[ "name" => "date", "type" => "datetime", "options" => "primary key" ],
				[ "name" => "item", "type" => "int", "options" => "primary key" ],
				[ "name" => "value", "type" => "int" ]
			]
		]);

		$this->assertNotFalse($table);

		$date = date("Y-m-d");
		$value0 = rand(10, 100);
		$value1 = rand(10, 100);
		$value2 = rand(10, 100);

		$table->insertOrUpdate([ "value" => $value0, "date" => $date, "item" => 1 ]);
		$table->insertOrUpdate([ "value" => $value1, "date" => $date, "item" => 1 ]);
		$table->insertOrUpdate([ "value" => $value2, "date" => $date, "item" => 2 ]);

		$result1 = $table->select("value where date = '$date' and item = 1");
		$result2 = $table->select("value where date = '$date' and item = 2");
		$this->assertEquals($value1, $result1[0]["value"]);
		$this->assertEquals($value2, $result2[0]["value"]);
	}

	public function test_insertOrUpdate_fails_if_all_primary_keys_are_not_present() {
		$table = \DataSaver\Table::model([
			"db" => "test",
			"name" => "test_update",
			"columns" => [
				[ "name" => "date", "type" => "datetime", "options" => "primary key" ],
				[ "name" => "item", "type" => "int", "options" => "primary key" ],
				[ "name" => "value", "type" => "int" ]
			]
		]);
		$this->assertNotFalse($table);

		$this->expectExceptionMessage("Update with primary key not declared: date");
		$result = $table->insertOrUpdate([ "value" => 1, "item" => 1 ]);
		$this->assertFalse($result);
	}

	public function test_insert_with_now() {
		$table = \DataSaver\Table::model([
			"db" => "test",
			"name" => "test_update_now",
			"columns" => [
				[ "name" => "id", "type" => "int", "options" => "auto_increment primary key" ],
				[ "name" => "datetime", "type" => "datetime" ],
				[ "name" => "date", "type" => "date" ],
				[ "name" => "time", "type" => "time" ]
			]
		]);
		$date = date("Y-m-d");
		$time = date("H:i:s");
		$datetime = "$date $time";
		$id = $table->insert([ "date" => 'now()', "time" => 'now()', "datetime" => 'now()' ]);
		$result = $table->select("datetime, date, time where id = $id");
		$this->assertCount(1, $result);
		$this->assertCount(3, array_keys($result[0]));
		$this->assertEquals($datetime, $result[0]["datetime"]);
		$this->assertEquals($date, $result[0]["date"]);
		$this->assertEquals($time, $result[0]["time"]);
	}

	public function test_insertOrUpdate_with_now() {
		$table = \DataSaver\Table::model([
			"db" => "test",
			"name" => "test_insertOrUpdate_now",
			"columns" => [
				[ "name" => "date", "type" => "date", "options" => "primary key" ],
				[ "name" => "value", "type" => "int" ]
			]
		]);

		$VALUE1 = 1;
		$VALUE2 = 2;

		$date = date("Y-m-d");
		$table->insertOrUpdate([ "date" => 'now()', "value" => $VALUE1 ]);
		$result = $table->select("date, value where date = '$date'");
		$this->assertCount(1, $result);
		$this->assertEquals($date, $result[0]["date"]);
		$this->assertEquals($VALUE1, $result[0]["value"]);

		$table->insertOrUpdate([ "date" => 'now()', "value" => $VALUE2 ]);
		$result = $table->select("date, value where date = '$date'");
		$this->assertCount(1, $result);
		$this->assertEquals($date, $result[0]["date"]);
		$this->assertEquals($VALUE2, $result[0]["value"]);
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

	public function test_truncate() {
		$table = \DataSaver\Table::model([
			"db" => "test",
			"name" => "test_truncate",
			"columns" => [
				[ "name" => "id", "type" => "int", "options" => "auto_increment primary key" ],
				[ "name" => "value", "type" => "varchar(50)" ]
			]
		]);
		$VALUE = "test";
		$table->insert([
			"value" => $VALUE
		]);
		$result = $table->select("value");
		$this->assertCount(1, $result);
		$this->assertEquals($VALUE, $result[0]["value"]);
		$this->assertTrue($table->truncate());
		$result = $table->select("value");
		$this->assertCount(0, $result);
	}

}

?>
