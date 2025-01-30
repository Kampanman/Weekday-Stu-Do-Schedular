<?php
include('../db.php');
include('../functions.php');
session_start();
$result = 0;

$_SESSION['page'] = "own_mypage_index";
$result = 1;

echo $result;
