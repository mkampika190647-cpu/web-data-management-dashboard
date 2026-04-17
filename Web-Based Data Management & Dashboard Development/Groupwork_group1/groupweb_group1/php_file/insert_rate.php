<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $mysqli = new mysqli("localhost", "root", "", "apartment");
    $mysqli->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    echo "❌ DB connect failed: ". $e->getMessage();
    exit;
}

// รับค่าจากฟอร์ม
$RateID          = isset($_POST['RateID']) ? trim($_POST['RateID']) : '';
$Electricity_unit= isset($_POST['Electricity_unit']) ? trim($_POST['Electricity_unit']) : '';
$Water_unit      = isset($_POST['Water_unit']) ? trim($_POST['Water_unit']) : '';
// ตรวจสอบข้อมูลครบถ้วน
if (
    $RateID   === '' || $Electricity_unit === '' ||$Water_unit   === '' 
) {
    echo "❌ Please fill all fields.";
    exit;
}

// เริ่ม Transaction
$mysqli->begin_transaction();

try {
   //Insert into rate
   $stmt_rate = $mysqli->prepare("INSERT INTO rate (RateID, Electricity_unit, Water_unit) VALUES (?, ?, ?)");
   $stmt_rate->bind_param("sss", $RateID, $Electricity_unit, $Water_unit);
   $stmt_rate->execute();
    // commit transaction
    $mysqli->commit();
    // ปิด statement
    $stmt_rate->close();
    $mysqli->close();
    echo "✅ rate saved successfully!";
} catch (Exception $e) {
    try { $mysqli->rollback(); } catch (Exception $ex) {}
    
    if (isset($stmt_rate) && $stmt_rate instanceof mysqli_stmt) {
        $stmt_ratr->close();
    }
    $mysqli->close();

    echo "❌ Failed to save Rate: " . $e->getMessage();
    exit;
}

