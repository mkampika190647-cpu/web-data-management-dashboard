<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/db.php';

$sql = "
  SELECT
    r.Room_Number,
    CASE
      WHEN c.CustomerID IS NOT NULL THEN 'OCCUPIED'
      ELSE 'VACANT'
    END AS status
  FROM room r
  LEFT JOIN customer c ON c.Room_Number = r.Room_Number
  GROUP BY r.Room_Number
";

$res = $mysqli->query($sql);
if (!$res) {
    echo json_encode(['error' => $mysqli->error]);
    exit;
}

$counts = ['OCCUPIED' => 0, 'VACANT' => 0];
while ($row = $res->fetch_assoc()) {
  $status = $row['status'];
  if (isset($counts[$status])) {
    $counts[$status]++;
  }
}

$out = [];
foreach ($counts as $status => $cnt) {
  $out[] = ['status' => $status, 'cnt' => $cnt];
}

echo json_encode($out);
exit;
