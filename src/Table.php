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
			$this->keys = [];
			for ($i = 0; $i < count($columns); $i++) {
				$options = isset($columns[$i]["options"]) ? $columns[$i]["options"] : null;
				if ($options) {
					if (strpos(strtolower($options), "primary key") !== false) {
						array_push($this->keys, $columns[$i]["name"]);
						$columns[$i]["options"] = str_replace("primary key", "", strtolower($options));
					}
				}
			}
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

		public function getKeys() {
			return $this->keys;
		}

		public function create() {
			$conn = $this->getConn();
			$columns = $this->getColumns();
			$queryArray = [];
			foreach ($columns as $column) {
				array_push($queryArray, $column["name"] . " " . $column["type"] . (isset($column["options"]) ? " " . $column["options"] : ""));
			}
			if ($this->getKeys()) {
				array_push($queryArray, "primary key (" . implode(",", $this->getKeys()) . ")");
			}
			$query = $conn->query("create table if not exists " . $this->getName() . " (" . implode(",", $queryArray) . ")");
			if (! $query) {
				throw new \Exception("Error on table creation: " . $conn->error);
			}
			return (bool)$query;
		}

		public function insert($data = [], $where = null) {

			if ($where) {
				$data = array_merge($data, $where);
			}

			$queryString = "insert into " . $this->getName() . " (" . implode(",", array_keys($data)) . ") values (" . implode(",", array_fill(0, count(array_keys($data)), "?")) . ")";

			if ($where) {
				$queryString .= " on duplicate key update ";
				$_where = [];
				foreach ($data as $key => $value) {
					array_push($_where, "$key = ?");
				}
				$queryString .= implode(",", $_where);
			}

			$querySymbols = [];
			$parsedValues = [];

			foreach ($data as $columnName => $value) {
				array_push($querySymbols, $this->getTypeSymbolForColumn($columnName));
				array_push($parsedValues, $value);
			}

			if ($where) {
				foreach ($data as $columnName => $value) {
					array_push($querySymbols, $this->getTypeSymbolForColumn($columnName));
					array_push($parsedValues, $value);
				}
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

		public function insertOrUpdate($data = []) {
			$keys = $this->getKeys();
			$where = [];
			foreach ($keys as $key) {
				if (! in_array($key, array_keys($data))) {
					throw new \Exception("Update with primary key not declared: $key");
				}
				$where[$key] = $data[$key];
				unset($data[$key]);
			}
			return $this->insert($data, $where);
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
