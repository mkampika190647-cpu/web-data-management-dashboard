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
$BillID          = isset($_POST['BillID']) ? trim($_POST['BillID']) : '';
$CustomerID      = isset($_POST['CustomerID']) ? trim($_POST['CustomerID']) : '';
$Electric_bill   = isset($_POST['Electric_bill']) ? trim($_POST['Electric_bill']) : '';
$Water_bill      = isset($_POST['Water_bill']) ? trim($_POST['Water_bill']) : '';
$Rental_bill     = isset($_POST['Rental_bill']) ? trim($_POST['Rental_bill']) : '';
$RateID          = isset($_POST['RateID']) ? trim($_POST['RateID']) : '';

// ตรวจสอบข้อมูลครบถ้วน
if (
    $BillID  === '' || $CustomerID  === '' || $Electric_bill === '' ||
    $Rental_bill  === '' || $RateID   === '' )
{
    echo "❌ Please fill all fields.";
    exit;
}

// เริ่ม Transaction
$mysqli->begin_transaction();

try {
    //  Insert into bill
    $stmt_bill = $mysqli->prepare("INSERT INTO bill (BillID, CustomerID, Electric_bill, Water_bill, Rental_bill, RateID) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_bill->bind_param("ssssss", $BillID, $CustomerID, $Electric_bill, $Water_bill, $Rental_bill, $RateID);
    $stmt_bill->execute();
    // commit transaction
    $mysqli->commit();
    // ปิด statement
    $stmt_bill->close();
    $mysqli->close();
    echo "✅ Bill saved successfully!";
} catch (Exception $e) {
    try { $mysqli->rollback(); } catch (Exception $ex) {}
    
    if (isset($stmt_bill) && $stmt_bill instanceof mysqli_stmt) {
        $stmt_bill->close();
    }
    $mysqli->close();

    echo "❌ Failed to save bill: " . $e->getMessage();
    exit;
}


