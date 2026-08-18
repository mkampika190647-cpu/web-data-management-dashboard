<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/db.php';

// ตั้ง charset เป็น utf8mb4
$mysqli->set_charset("utf8mb4");

$sql = "
  SELECT 
    b.BillID,
    b.CustomerID,
    b.Electric_bill,
    b.Water_bill,
    b.Rental_bill,
    (b.Electric_bill + b.Water_bill + b.Rental_bill) AS Total
  FROM bill b
  ORDER BY b.BillID
";

$res = $mysqli->query($sql);

if (!$res) {
  // กรณี query error ส่งข้อความ error กลับไป
  echo json_encode([
    'error' => $mysqli->error
  ]);
  exit;
}

$electric = [];
$water = [];
$rental = [];
$total = [];

while ($row = $res->fetch_assoc()) {
  $electric[] = (float)$row['Electric_bill'];
  $water[] = (float)$row['Water_bill'];
  $rental[] = (float)$row['Rental_bill'];
  $total[] = (float)$row['Total'];
}

echo json_encode([
  'electric' => $electric,
  'water' => $water,
  'rental' => $rental,
  'total' => $total
]);
?>
