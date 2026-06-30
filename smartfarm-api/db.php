<?php
$host = 'localhost';
$username = 'root'; // User เริ่มต้นของ XAMPP
$password = ''; // Password เริ่มต้นของ XAMPP จะว่างเปล่า
$database = 'api_demo'; // เปลี่ยนชื่อฐานข้อมูลให้ตรงกับที่คุณใช้ใน phpMyAdmin

// สร้างการเชื่อมต่อด้วย mysqli
$conn = new mysqli($host, $username, $password, $database);

// เช็คการเชื่อมต่อ
if ($conn->connect_error) {
    header("Content-Type: application/json; charset=UTF-8");
    http_response_code(500);
    die(json_encode([
        "success" => false,
        "message" => "Database connection failed: " . $conn->connect_error
    ]));
}

// กำหนด charset เป็น utf8 เพื่อรองรับภาษาไทยและการเข้ารหัสที่ถูกต้อง
$conn->set_charset("utf8mb4");
?>
