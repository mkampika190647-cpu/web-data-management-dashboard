<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/db.php';

// รับค่าห้องที่เลือก (ถ้ามี)
$room = isset($_GET['room']) ? trim($_GET['room']) : '';

// 1️⃣ จำนวนห้องทั้งหมด
$roomsRow = $mysqli->query("SELECT COUNT(*) AS n FROM room")->fetch_assoc();
$rooms = (int)$roomsRow['n'];

if ($room === '') {
  // 2️⃣ จำนวนผู้เช่าทั้งหมด (ทุกห้อง)
  $customersRow = $mysqli->query("SELECT COUNT(*) AS n FROM customer")->fetch_assoc();
  $customers = (int)$customersRow['n'];

  // 3️⃣ ยอดรวมบิลทั้งหมด (ค่าไฟ + ค่าน้ำ + ค่าเช่า)
  $billRow = $mysqli->query("
    SELECT SUM(Electric_bill + Water_bill + Rental_bill) AS totalBill
    FROM bill
  ")->fetch_assoc();
  $total = $billRow['totalBill'] !== null ? (float)$billRow['totalBill'] : 0;

} else {
  // 4️⃣ จำนวนผู้เช่าตามห้องที่เลือก
  $stmt = $mysqli->prepare("SELECT COUNT(*) AS n FROM customer WHERE Room_Number = ?");
  $stmt->bind_param("s", $room);
  $stmt->execute();
  $customers = (int)$stmt->get_result()->fetch_assoc()['n'];
  $stmt->close();

  // 5️⃣ ยอดรวมบิลเฉพาะห้องที่เลือก (join กับ customer)
  $stmt = $mysqli->prepare("
    SELECT SUM(b.Electric_bill + b.Water_bill + b.Rental_bill) AS totalBill
    FROM bill b
    JOIN customer c ON b.CustomerID = c.CustomerID
    WHERE c.Room_Number = ?
  ");
  $stmt->bind_param("s", $room);
  $stmt->execute();
  $billRow = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  $total = $billRow['totalBill'] !== null ? (float)$billRow['totalBill'] : 0;
}

// ส่งข้อมูลกลับเป็น JSON
echo json_encode([
  'rooms' => $rooms,
  'customers' => $customers,
  'total' => $total
]);
exit;
