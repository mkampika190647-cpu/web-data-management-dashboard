<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/db.php';

$res = $mysqli->query("SELECT Room_Number FROM room ORDER BY Room_Number");
$out = [];
while ($row = $res->fetch_assoc()) {
  $out[] = (int)$row['Room_Number'];
}
echo json_encode($out);
?>
