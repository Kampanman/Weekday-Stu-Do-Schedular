<?php

$dsn = 'localhost';
$dbname = 'crud';
$username = 'root';
$password = '';
$connection = new PDO('mysql:host=' . $dsn . ';dbname=' . $dbname, $username, $password);
$account_table = 'wsds_accounts';
$data_table = 'wsds_notes';
