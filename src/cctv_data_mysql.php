<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$host = "";
$port = 3306;
$user = "";
$password = "";
$database = ""; 

$conn = new mysqli($host, $user, $password, $database, $port);
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

$sql = "SELECT cam_no, camera_name, Latitude, Longitude, Location, Project, SubPro, NewCode, NVR, Contract, 
               cctv_Online_status, online_cause, online_other_cause, cctv_position, position_symptom, position_other_symptom, 
               cctv_clear, clear_symptom, clear_other_symptom, cctv_record, record_symptom, record_other_symptom, 
               cctv_Overall_status, `Last Update`, `Use Status`, Use_Status_Date, Not_Use_date, Label, Offline_duration
        FROM Device_info";

$result = $conn->query($sql);

$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

$conn->close();

echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
