<?php 

	class DbConnect{

		private $con; 

		function __construct(){

		}

		function connect(){
			//include_once __DIR__ . '/../config/Constants.php';
			include_once 'config.php';
			$this->con = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

			return $this->con; 
		}
	}