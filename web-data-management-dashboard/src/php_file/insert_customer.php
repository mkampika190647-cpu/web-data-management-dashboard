<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$mysqli = new mysqli("localhost","root","","apartment");
$mysqli->set_charset("utf8mb4");

$CustomerID = $_POST['CustomerID'] ?? '';
$Room_Number = $_POST['Room_Number'] ?? '';
$Contract_date = $_POST['Contract_date'] ?? '';
$Note = $_POST['Note'] ?? '';
$RoomPayment_date = $_POST['RoomPayment_date'] ?? '';

if($CustomerID==''||$Room_Number==''||$Contract_date==''||$Note==''||$RoomPayment_date==''){
    echo "❌ Please fill all fields.";
    exit;
}

try{
    $mysqli->begin_transaction();

    // ตรวจสอบว่ามี Room_Number นี้อยู่ใน room table หรือไม่
    $check = $mysqli->prepare("SELECT 1 FROM room WHERE Room_Number=?");
    $check->bind_param("s",$Room_Number);
    $check->execute();
    $check->store_result();
    if($check->num_rows === 0){
        echo "❌ Room number not found. Please insert room first.";
        $check->close();
        exit;
    }
    $check->close();

    // insert customer
    $stmt_customer = $mysqli->prepare("INSERT INTO customer (CustomerID, Room_Number, Contract_date, Note, RoomPayment_date) VALUES (?,?,?,?,?)");
    $stmt_customer->bind_param("sssss",$CustomerID,$Room_Number,$Contract_date,$Note,$RoomPayment_date);
    $stmt_customer->execute();

    $mysqli->commit();
    $stmt_customer->close();
    $mysqli->close();
    echo "✅ Customer saved successfully!";
}catch(Exception $e){
    $mysqli->rollback();
    if(isset($stmt_customer)) $stmt_customer->close();
    $mysqli->close();
    echo "❌ Failed to save customer: ".$e->getMessage();
}
