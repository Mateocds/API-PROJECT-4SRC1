<?php
header("Content-Type: application/json");
include 'API/Alerting.php';
require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();