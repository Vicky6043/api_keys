<?php
header("Content-Type: application/json");

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Database Connection
$serverName = "192.168.50.30";
$connectionOptions = array(
    "Database" => "SKTPayroll",
    "Uid" => "sa",
    "PWD" => "ktm@werty123",
    "CharacterSet" => "UTF-8"
);

$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) {
    echo json_encode([
        "status" => "error",
        "message" => "Connection failed",
        "error_details" => sqlsrv_errors()
    ]);
    exit;
}

// Get Input Parameters Safely
$FromDate = isset($_GET['FromDate']) ? trim($_GET['FromDate']) : '';
$ToDate = isset($_GET['ToDate']) ? trim($_GET['ToDate']) : '';
$EmpCode = isset($_GET['EmpCode']) ? trim($_GET['EmpCode']) : '';

if (empty($EmpCode) || empty($FromDate) || empty($ToDate)) {
    echo json_encode([
        "status" => "error",
        "message" => "EmpCode, FromDate, and ToDate are required."
    ]);
    exit;
}

// Secure SQL Query with Parameterized Queries
$sql = "SELECT COUNT(att.AttENDanceId) AS Cnt,SUM(CASE WHEN att.Present='Y' THEN 1 ELSE 0 END ) as PrCnt,
        SUM(CASE WHEN att.HalfDayPresent='Y' THEN 0.5 WHEN att.HalfDayPresent='S' THEN 0.5 ELSE 0 END ) as halfcnt,
        SUM(CASE WHEN att.Present = 'Y' THEN 1 ELSE 0 END) + 
        SUM(CASE WHEN att.HalfDayPresent = 'Y' THEN 0.5 WHEN att.HalfDayPresent = 'S' THEN 0.5 ELSE 0 END) AS TotalPrCnt
        FROM EmployeeAttENDance att
        INNER JOIN EmployeeMaster emp ON emp.EmployeeId=att.EmployeeId
        WHERE emp.BiometricCode = ? 
        AND att.AttendanceDate BETWEEN ? AND ?";

$params = [$EmpCode, $FromDate, $ToDate];
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    echo json_encode([
        "status" => "error",
        "message" => "Query failed",
        "error_details" => sqlsrv_errors()
    ]);
    exit;
}

$data = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $data[] = $row;
}

if (empty($data)) {
    echo json_encode([
        "status" => "error",
        "message" => "No records found for the given Employee Code and Date Range."
    ]);
} else {
    echo json_encode([
        "status" => "success",
        "message" => "Employee attendance retrieved successfully!",
        "data" => $data
    ]);
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
