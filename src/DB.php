<?php

	namespace DataSaver;

	use \DataSaver\Conn;

	class DB {

		public function __construct($dbname) {
			$this->dbname = $dbname;
			$this->conn = Conn::get();
		}

		public function getDBName() {
			return $this->dbname;
		}

		public function create() {
			$query = $this->conn->query("create database if not exists " . $this->getDBName());
			if (! $query) {
				throw new \Exception("Error on query execution: " . $this->conn->error);
			}
			$this->conn->close();
			return (bool)$query;
		}

	}

?>
