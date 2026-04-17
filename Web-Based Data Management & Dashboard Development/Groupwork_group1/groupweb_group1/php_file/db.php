<?php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = ''; // เปลี่ยนตามเครื่องคุณ
$DB_NAME = 'apartment'; // ชื่อฐานข้อมูลของคุณ

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
  http_response_code(500);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['error' => $mysqli->connect_error]);
  exit;
}
$mysqli->set_charset('utf8mb4');
