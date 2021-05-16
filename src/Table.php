<?php

	namespace DataSaver;

	use \DataSaver\Conn;
	use \DataSaver\DB;

	class Table {

		public static function model($data = []) {
			if (! isset($data["db"]) || ! isset($data["name"]) || ! isset($data["columns"])) {
				return false;
			}

			$dbname = $data["db"];
			$name = $data["name"];
			$columns = $data["columns"];

			$db = new DB($dbname);
			if (! $db->create()) {
				return false;
			}

			$table = new Table($dbname, $name, $columns);
			if (! $table->create()) {
				return false;
			}

			return $table;
		}

		public function __construct($dbname, $name, $columns) {
			$this->dbname = $dbname;
			$this->name = $name;
			$this->columns = $columns;
			$this->conn = null;
		}

		public function getColumn($columnName) {
			foreach ($this->columns as $column) {
				if ($column["name"] == $columnName) {
					return $column;
				}
			}
		}

		public function getTypeSymbolForColumn($columnName) {
			$column = $this->getColumn($columnName);
			$type = strtolower($column["type"]);

			$DEFS = [
				"s" => [ "char", "text", "date", "time" ],
				"i" => [ "int" ],
				"b" => [ "blob" ],
				"d" => [ "float", "double" ]
			];

			foreach ($DEFS as $symbol => $types) {
				foreach ($types as $_type) {
					if (strpos($type, $_type) !== false) {
						return $symbol;
					}
				}
			}
		}

		public function getDbName() {
			return $this->dbname;
		}

		public function getName() {
			return $this->name;
		}

		public function getColumns() {
			return $this->columns;
		}

		public function create() {
			$conn = $this->getConn();
			$columns = $this->getColumns();
			$queryArray = [];
			foreach ($columns as $column) {
				array_push($queryArray, $column["name"] . " " . $column["type"] . (isset($column["options"]) ? " " . $column["options"] : ""));
			}
			$query = $conn->query("create table if not exists " . $this->getName() . " (" . implode(",", $queryArray) . ")");
			if (! $query) {
				throw new \Exception("Error on table creation: " . $conn->error);
			}
			return (bool)$query;
		}

		public function insert($data = []) {
			$queryString = "insert into " . $this->getName() . " (" . implode(",", array_keys($data)) . ") values (" . implode(",", array_fill(0, count(array_keys($data)), "?")) . ")";
			$querySymbols = [];
			$parsedValues = [];

			foreach ($data as $columnName => $value) {
				array_push($querySymbols, $this->getTypeSymbolForColumn($columnName));
				array_push($parsedValues, $value);
			}

			$conn = $this->getConn();
			$st = $conn->prepare($queryString);
			if ($st) {
				$st->bind_param(implode("", $querySymbols), ...$parsedValues);
				if ($st->execute()) {
					return $conn->insert_id;
				} else {
					throw new \Exception("Error on query execution: " . $conn->error);
				}
			} else {
				throw new \Exception("Error on query execution: " . $conn->error);
			}
		}

		public function select($pseudoQuery) {
			$conn = $this->getConn();
			$pseudoValues = explode(" where ", $pseudoQuery);
			$query = "select " . $pseudoValues[0] . " from " . $this->getName();
			if (count($pseudoValues) == 2) {
				$query .= " where " . $pseudoValues[1];
			}

			if ($exec = $conn->query($query)) {
			$result = [];
				while ($rows = $exec->fetch_array()) {
					array_push($result, $rows);
				}
				return $result;
			} else {
				throw new \Exception("Error on query execution: " . $conn->error);
			}
		}

		public function getConn() {
			if (! $this->conn) {
				$this->conn = Conn::get($this->getDbName());
			}
			return $this->conn;
		}

	}

?>
