<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $mysqli = new mysqli("localhost", "root", "", "apartment");
    $mysqli->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    echo "❌ DB connect failed: " . $e->getMessage();
    exit;
}

// รับค่าจากฟอร์ม
$Room_Number = isset($_POST['Room_Number']) ? trim($_POST['Room_Number']) : '';
$Room_size   = isset($_POST['Room_size']) ? trim($_POST['Room_size']) : '';
$Room_count  = isset($_POST['Room_count']) ? trim($_POST['Room_count']) : '';
$Amenities   = isset($_POST['Amenities']) ? trim($_POST['Amenities']) : '';

// ตรวจสอบข้อมูลครบถ้วน
if ($Room_Number === '' || $Room_size === '' || $Room_count === '' || $Amenities === '') {
    echo "❌ Please fill all fields.";
    exit;
}

try {
    $mysqli->begin_transaction();

    // Insert into room
    $stmt_room = $mysqli->prepare("INSERT INTO room (Room_Number, Room_size, Room_count, Amenities) VALUES (?, ?, ?, ?)");
    $stmt_room->bind_param("ssis", $Room_Number, $Room_size, $Room_count, $Amenities);
    $stmt_room->execute();

    // commit transaction
    $mysqli->commit();

    // ปิด statement และ connection
    $stmt_room->close();
    $mysqli->close();

    echo "✅ Room saved successfully!";
} catch (Exception $e) {
    // rollback transaction โดยไม่ใช้ in_transaction
    try { $mysqli->rollback(); } catch (Exception $ex) {}
    
    if (isset($stmt_room) && $stmt_room instanceof mysqli_stmt) {
        $stmt_room->close();
    }
    $mysqli->close();

    echo "❌ Failed to save Room: " . $e->getMessage();
    exit;
}
