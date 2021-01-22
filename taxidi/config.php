<?php
session_start();
ob_start(); //turns on output buffering

$dbuser = "lucidboi_tax_app";
$dbpass = "3N]k!TbQapf_";
$dbname = "lucidboi_test_taxidi";

$con = mysqli_connect("localhost", $dbuser, $dbpass, $dbname); //connection variable

if(mysqli_connect_errno())
{
	echo "Failed to Connect: " , mysqli_connect_errno();
}

?>