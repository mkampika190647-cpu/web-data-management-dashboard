<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/db.php';

$mysqli->set_charset("utf8mb4");

$room = isset($_GET['room']) ? trim($_GET['room']) : '';

$sql = "
  SELECT 
    c.CustomerID,
    c.Room_Number,
    COALESCE((
      SELECT SUM(Electric_bill + Water_bill + Rental_bill)
      FROM bill b
      WHERE b.CustomerID = c.CustomerID
    ), 0) AS Total_Bill
  FROM customer c
";

$params = [];
$types = "";
if ($room !== '') {
  if (!ctype_digit($room)) {
    // ถ้า room ไม่ใช่ตัวเลข ส่งกลับ error ง่ายๆ
    echo json_encode(['error' => 'Invalid room number']);
    exit;
  }
  $sql .= " WHERE c.Room_Number = ?";
  $params[] = (int)$room;
  $types .= "i";  // Room_Number เป็น int
}
$sql .= " ORDER BY c.Room_Number";

if ($room !== '') {
  $stmt = $mysqli->prepare($sql);
  if (!$stmt) {
    echo json_encode(['error' => $mysqli->error]);
    exit;
  }
  $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $res = $stmt->get_result();
} else {
  $res = $mysqli->query($sql);
  if (!$res) {
    echo json_encode(['error' => $mysqli->error]);
    exit;
  }
}

$out = [];
while ($row = $res->fetch_assoc()) {
  $out[] = [
    'CustomerID'  => $row['CustomerID'],
    'Room_Number' => (int)$row['Room_Number'],
    'Total_Bill'  => (float)$row['Total_Bill']
  ];
}

echo json_encode($out);
exit;
?>
