<?php

	namespace DataSaver;

	class Conn {

		public static function DEFAULT_HOST() {
			return "127.0.0.1";
		}

		public static function DEFAULT_USER() {
			return "root";
		}

		public static function DEFAULT_PASS() {
			return "";
		}

		public static function get($dbname = null) {
			$host = self::DEFAULT_HOST();
			$user = self::DEFAULT_USER();
			$pass = self::DEFAULT_PASS();
			$conn = @new \mysqli($host, $user, $pass);
			if ($conn->connect_error) {
				throw new \Exception("Error while establishing connection: " . $conn->connect_error);
			} else {
				if ($dbname) {
					$conn->select_db($dbname);
				}
				return $conn;
			}
		}

	}

?>
