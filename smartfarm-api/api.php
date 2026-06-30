<?php
// ตั้งค่า Header สำหรับ RESTful API และให้รองรับ JSON Format
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// เชื่อมต่อฐานข้อมูล
require_once 'db.php';

// ดึง HTTP Method (GET, POST, PUT, DELETE)
$method = $_SERVER['REQUEST_METHOD'];

// จัดการ Request Path ที่ส่งมาจาก .htaccess (เช่น api/devices หรือ api/sensors)
$request_path = isset($_GET['request']) ? trim($_GET['request'], '/') : '';
$request = explode('/', $request_path);

// ดึงข้อมูล JSON Body จาก Request (ใช้สำหรับ POST, PUT)
$input = json_decode(file_get_contents('php://input'), true);

// ฟังก์ชันสำหรับส่ง Response กลับไปเป็น JSON Standard
function sendResponse($success, $data_or_message, $status_code = 200) {
    http_response_code($status_code);
    if ($success && is_array($data_or_message)) {
        echo json_encode(["success" => true, "data" => $data_or_message]);
    } else {
        echo json_encode(["success" => $success, "message" => $data_or_message]);
    }
    exit();
}

// Router อย่างง่าย สำหรับตรวจสอบ Endpoint
// รูปแบบ $request: [0] => 'api', [1] => 'devices', [2] => {id}
$api_prefix = isset($request[0]) ? $request[0] : '';
$endpoint = isset($request[1]) ? $request[1] : '';
$id = isset($request[2]) ? (int)$request[2] : null;

// ถ้าเรียกเข้ามาโดยไม่มี /api/ ให้นำไปเช็คเป็น endpoint เลยก็ได้ (เผื่อผู้ใช้ไม่ได้พิมพ์ /api/)
if ($api_prefix !== 'api') {
    $endpoint = $api_prefix;
    $id = isset($request[1]) ? (int)$request[1] : null;
}

switch ($endpoint) {
    case 'devices':
        handleDevices($method, $conn, $request, $input);
        break;
        
    case 'sensors':
        handleSensors($method, $conn, $request, $input);
        break;
        
    default:
        sendResponse(false, "Endpoint Not Found", 404);
}

// --- Controller Functions ---

// 1. จัดการ API Endpoint: /devices
function handleDevices($method, $conn, $request, $input) {
    // หา sub_route (เช่น /devices/history หรือ /devices/1)
    $api_prefix = isset($request[0]) ? $request[0] : '';
    $index_offset = ($api_prefix === 'api') ? 2 : 1;
    $sub_route = isset($request[$index_offset]) ? $request[$index_offset] : null;
    
    if ($method === 'GET') {
        if ($sub_route === 'history') {
            // GET /api/devices/history : ดึงประวัติการเปิด-ปิดอุปกรณ์ (จำกัด 100 รายการล่าสุด)
            $sql = "SELECT id, led_name as device_name, status, datetime FROM led_status ORDER BY datetime DESC LIMIT 100";
            $result = $conn->query($sql);
            
            $history = [];
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $history[] = $row;
                }
            }
            sendResponse(true, $history);
        } else {
            // GET /api/devices : ดึงข้อมูลอุปกรณ์ทั้งหมด
            $sql = "SELECT id, device_name, device_type, pin, status, created_at FROM devices";
            $result = $conn->query($sql);
            
            $devices = [];
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $devices[] = $row;
                }
            }
            sendResponse(true, $devices);
        }

        
    } elseif ($method === 'PUT') {
        // PUT /api/devices/{id} : อัปเดตสถานะ LED
        $id = (int)$sub_route;
        if (!$id) {
            sendResponse(false, "Device ID is required", 400);
        }
        
        if (!isset($input['status'])) {
            sendResponse(false, "Missing 'status' in request body", 400);
        }
        
        $status = strtoupper($input['status']); // ปรับให้เป็นตัวพิมพ์ใหญ่ (ON/OFF)
        if (!in_array($status, ['ON', 'OFF'])) {
             sendResponse(false, "Invalid status. Must be ON or OFF.", 400);
        }
        
        // ตรวจสอบก่อนว่าอุปกรณ์ที่ต้องการอัปเดตมีอยู่จริง และเป็น LED
        $check_sql = "SELECT device_name, device_type FROM devices WHERE id = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            sendResponse(false, "Device not found", 404);
        }
        
        $device = $result->fetch_assoc();
        if ($device['device_type'] !== 'LED') {
            sendResponse(false, "Only LED devices can have their status updated", 400);
        }
        $led_name = $device['device_name'];
        $stmt->close();
        
        // ทำการอัปเดตสถานะ (ใช้ Prepared Statement ป้องกัน SQL Injection)
        $update_sql = "UPDATE devices SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("si", $status, $id);
        
        if ($stmt->execute()) {
            // บันทึกประวัติการเปลี่ยนแปลงลงในตาราง led_status ด้วย
            $log_sql = "INSERT INTO led_status (led_name, status) VALUES (?, ?)";
            $log_stmt = $conn->prepare($log_sql);
            $log_stmt->bind_param("ss", $led_name, $status);
            $log_stmt->execute();
            $log_stmt->close();
            
            sendResponse(true, "LED status updated successfully");
        } else {
            sendResponse(false, "Failed to update device status", 500);
        }
        $stmt->close();
        
    } else {
        sendResponse(false, "Method Not Allowed for this endpoint", 405);
    }
}

// 2. จัดการ API Endpoint: /sensors
function handleSensors($method, $conn, $request, $input) {
    // หา sub_route (เช่น /sensors/history)
    $api_prefix = isset($request[0]) ? $request[0] : '';
    $index_offset = ($api_prefix === 'api') ? 2 : 1;
    $sub_route = isset($request[$index_offset]) ? $request[$index_offset] : null;

    if ($method === 'GET') {
        if ($sub_route === 'history') {
            // GET /api/sensors/history : ดึงประวัติข้อมูลเซ็นเซอร์ย้อนหลัง (100 รายการล่าสุด)
            $sql = "SELECT id, sensor_name, sensor_value, unit, datetime FROM sensor_data ORDER BY datetime DESC LIMIT 100";
            $result = $conn->query($sql);
            
            $history = [];
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $history[] = $row;
                }
            }
            sendResponse(true, $history);
        } else {
            // GET /api/sensors : ดึงค่าล่าสุดของเซนเซอร์แต่ละตัว
            $sql = "SELECT s1.id, s1.sensor_name, s1.sensor_value, s1.unit, s1.datetime 
                    FROM sensor_data s1
                    INNER JOIN (
                        SELECT sensor_name, MAX(datetime) as max_datetime 
                        FROM sensor_data 
                        GROUP BY sensor_name
                    ) s2 
                    ON s1.sensor_name = s2.sensor_name AND s1.datetime = s2.max_datetime
                    ORDER BY s1.sensor_name";
                    
            $result = $conn->query($sql);
            
            $sensors = [];
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $sensors[] = $row;
                }
            }
            sendResponse(true, $sensors);
        }
        
    } elseif ($method === 'POST') {
        // POST /api/sensors : เพิ่มข้อมูลเซนเซอร์ใหม่
        if (!isset($input['sensor_name']) || !isset($input['sensor_value']) || !isset($input['unit'])) {
            sendResponse(false, "Missing required fields: sensor_name, sensor_value, unit", 400);
        }
        
        $sensor_name = trim($input['sensor_name']);
        $sensor_value = (float)$input['sensor_value'];
        $unit = trim($input['unit']);
        
        // เช็คว่ามีการส่ง datetime มาด้วยไหม (สำหรับสร้างข้อมูลเทสย้อนหลัง)
        if (isset($input['datetime'])) {
            $datetime = $input['datetime'];
            $sql = "INSERT INTO sensor_data (sensor_name, sensor_value, unit, datetime) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sdss", $sensor_name, $sensor_value, $unit, $datetime);
        } else {
            // ใช้ Prepared Statement แบบปกติ
            $sql = "INSERT INTO sensor_data (sensor_name, sensor_value, unit) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sds", $sensor_name, $sensor_value, $unit);
        }
        
        if ($stmt->execute()) {
            sendResponse(true, "Sensor data recorded successfully", 201);
        } else {
            sendResponse(false, "Failed to record sensor data", 500);
        }
        $stmt->close();
        
    } else {
        sendResponse(false, "Method Not Allowed for this endpoint", 405);
    }
}
?>
